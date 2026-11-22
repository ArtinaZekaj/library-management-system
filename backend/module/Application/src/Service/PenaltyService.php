<?php

namespace Application\Service;

use Application\Entity\Loan;
use Application\Entity\Penalty;
use Application\Entity\SystemSetting;
use Application\Entity\User;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use DomainException;
use RuntimeException;

class PenaltyService
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function hasUnpaidPenalties(User $student): bool
    {
        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Penalty::class, 'p')
            ->join('p.loan', 'l')
            ->where('l.user = :student')
            ->andWhere('p.paid = :paid')
            ->setParameter('student', $student)
            ->setParameter('paid', false)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    public function applyOnReturn(
        Loan $loan,
        DateTimeInterface $returnedAt
    ): array {
        $utc = new DateTimeZone('UTC');

        $dueDate = DateTimeImmutable::createFromInterface(
            $loan->getDue_at()
        )->setTimezone($utc)->setTime(0, 0);

        $returnDate = DateTimeImmutable::createFromInterface(
            $returnedAt
        )->setTimezone($utc)->setTime(0, 0);

        $daysLate = max(
            0,
            (int) $dueDate->diff($returnDate)->format('%r%a')
        );

        if ($daysLate === 0) {
            return [
                'days_late' => 0,
                'amount' => '0.00',
                'has_unpaid_penalty' => false,
            ];
        }

        $setting = $this->entityManager
            ->getRepository(SystemSetting::class)
            ->find('penalty_rate');

        if ($setting === null) {
            throw new RuntimeException('Missing penalty rate');
        }

        $rate = $setting->getValue();

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $rate)) {
            throw new RuntimeException('Invalid penalty rate');
        }

        $parts = explode('.', $rate);

        $rateCents = (int) $parts[0] * 100
            + (int) str_pad($parts[1] ?? '0', 2, '0');

        $amountCents = $daysLate * $rateCents;

        $amount = sprintf(
            '%d.%02d',
            intdiv($amountCents, 100),
            $amountCents % 100
        );

        if ($amountCents === 0) {
            return [
                'days_late' => $daysLate,
                'amount' => '0.00',
                'has_unpaid_penalty' => false,
            ];
        }

        $penalty = new Penalty();
        $penalty->setLoan($loan);
        $penalty->setAmount($amount);
        $penalty->setDaysLate($daysLate);
        $penalty->setPaid(false);

        $this->entityManager->persist($penalty);

        return [
            'days_late' => $daysLate,
            'amount' => $amount,
            'has_unpaid_penalty' => true,
        ];
    }

    public function payPenalty(
        User $actor,
        int $penaltyId
    ): array {
        return $this->entityManager->wrapInTransaction(
            function (EntityManager $em) use (
                $actor,
                $penaltyId
            ): array {
                $penalty = $em->find(
                    Penalty::class,
                    $penaltyId,
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($penalty === null) {
                    throw new DomainException(
                        'Penalty not found',
                        404
                    );
                }

                $loan = $em->find(
                    Loan::class,
                    $penalty->getLoan()->getId(),
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($loan === null) {
                    throw new RuntimeException(
                        'Loan not found'
                    );
                }

                $role = $actor->getRole();

                if (!in_array(
                    $role,
                    ['student', 'operator', 'super_admin'],
                    true
                )) {
                    throw new DomainException(
                        'Access denied',
                        403
                    );
                }

                if (
                    $role === 'student' &&
                    $loan->getUser()->getId() !== $actor->getId()
                ) {
                    throw new DomainException(
                        'Access denied',
                        403
                    );
                }

                if ($penalty->isPaid()) {
                    throw new DomainException(
                        'Penalty has already been paid',
                        409
                    );
                }

                if (
                    $loan->getReturnedAt() === null ||
                    $loan->getStatus() !== 'awaiting_payment'
                ) {
                    throw new DomainException(
                        'Loan is not awaiting payment',
                        409
                    );
                }

                $now = new DateTime(
                    'now',
                    new DateTimeZone('UTC')
                );

                $penalty->setPaid(true);
                $penalty->setPaidAt($now);

                $loan->setStatus('closed');

                return [
                    'penalty_id' => $penaltyId,
                    'loan_id' => $loan->getId(),
                    'amount' => $penalty->getAmount(),
                    'paid' => true,
                    'paid_at' => $now->format(DATE_ATOM),
                    'loan_status' => $loan->getStatus(),
                ];
            }
        );
    }
}
