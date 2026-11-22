<?php

namespace Application\Service;

use Application\Entity\Book;
use Application\Entity\Loan;
use Application\Entity\SystemSetting;
use Application\Entity\User;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use DomainException;
use RuntimeException;

class LoanService
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function borrowBook(User $student, int $bookId): array
    {
        if ($student->getRole() !== 'student') {
            throw new DomainException(
                'Only students can borrow books',
                403
            );
        }

        return $this->entityManager->wrapInTransaction(
            function (EntityManager $em) use ($student, $bookId): array {

                $lockedStudent = $em->find(
                    User::class,
                    $student->getId(),
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($lockedStudent === null) {
                    throw new DomainException(
                        'Student not found',
                        404
                    );
                }

                $book = $em->find(
                    Book::class,
                    $bookId,
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($book === null) {
                    throw new DomainException(
                        'Book not found',
                        404
                    );
                }

                if ($book->getStock() <= 0) {
                    throw new DomainException(
                        'Book unavailable',
                        409
                    );
                }

                $limit = (int) $this->getSetting('borrowing_limit');
                $period = (int) $this->getSetting('borrowing_period');
                $fee = $this->getSetting('borrow_fee');

                if (
                    $limit < 1 ||
                    $period < 1 ||
                    !preg_match('/^\d+(\.\d{1,2})?$/', $fee)
                ) {
                    throw new RuntimeException(
                        'Invalid borrowing settings'
                    );
                }

                $activeLoans = (int) $em->createQueryBuilder()
                    ->select('COUNT(l.id)')
                    ->from(Loan::class, 'l')
                    ->where('l.user = :student')
                    ->andWhere('l.returned_at IS NULL')
                    ->setParameter('student', $lockedStudent)
                    ->getQuery()
                    ->getSingleScalarResult();

                if ($activeLoans >= $limit) {
                    throw new DomainException(
                        'Borrowing limit reached',
                        409
                    );
                }

                $loan = new Loan();
                $loan->setUser($lockedStudent);
                $loan->setBook($book);

                $dueAt = clone $loan->getBorrowed();
                $dueAt->modify('+' . $period . ' days');

                $loan->setDue_at($dueAt);
                $loan->setStatus('active');

                $book->setStock($book->getStock() - 1);

                $em->persist($loan);

                return [
                    'book_id' => $book->getId(),
                    'borrowed_at' => $loan->getBorrowed()
                        ->format(DATE_ATOM),
                    'due_at' => $loan->getDue_at()
                        ->format(DATE_ATOM),
                    'status' => $loan->getStatus(),
                    'borrow_fee' => $fee,
                    'remaining_stock' => $book->getStock(),
                ];
            }
        );
    }

    public function returnBook(int $loanId): array
    {
        return $this->entityManager->wrapInTransaction(
            function (EntityManager $em) use ($loanId): array {

                $loan = $em->find(
                    Loan::class,
                    $loanId,
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($loan === null) {
                    throw new DomainException(
                        'Loan not found',
                        404
                    );
                }

                if ($loan->getReturnedAt() !== null) {
                    throw new DomainException(
                        'Book has already been returned',
                        409
                    );
                }

                $timezone = new DateTimeZone('UTC');
                $now = new DateTime('now', $timezone);

                $today = DateTimeImmutable::createFromMutable($now)
                    ->setTime(0, 0);

                $dueDate = DateTimeImmutable::createFromMutable(
                    $loan->getDue_at()
                )->setTimezone($timezone)->setTime(0, 0);

                if ($today > $dueDate) {
                    throw new DomainException(
                        'Overdue return requires penalty processing',
                        409
                    );
                }

                $book = $em->find(
                    Book::class,
                    $loan->getBook()->getId(),
                    LockMode::PESSIMISTIC_WRITE
                );

                if ($book === null) {
                    throw new RuntimeException(
                        'Book not found'
                    );
                }

                $loan->setReturnedAt($now);
                $loan->setStatus('closed');

                $book->setStock($book->getStock() + 1);

                return [
                    'loan_id' => $loan->getId(),
                    'book_id' => $book->getId(),
                    'returned_at' => $now->format(DATE_ATOM),
                    'status' => $loan->getStatus(),
                    'penalty' => '0.00',
                    'remaining_stock' => $book->getStock(),
                ];
            }
        );
    }

    public function listLoans(User $currentUser): array
    {
        $role = $currentUser->getRole();

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

        $qb = $this->entityManager->createQueryBuilder()
            ->select('l', 'b', 'u')
            ->from(Loan::class, 'l')
            ->leftJoin('l.book', 'b')
            ->leftJoin('l.user', 'u')
            ->orderBy('l.borrowed_at', 'DESC');

        if ($role === 'student') {
            $qb->where('l.user = :student')
                ->setParameter('student', $currentUser);
        }

        $loans = $qb->getQuery()->getResult();

        $today = (new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        ))->setTime(0, 0);

        $result = [];

        foreach ($loans as $loan) {
            $dueDate = DateTimeImmutable::createFromMutable(
                $loan->getDue_at()
            )->setTimezone(
                new DateTimeZone('UTC')
            )->setTime(0, 0);

            $isOverdue = $loan->getReturnedAt() === null
                && $today > $dueDate;

            $daysOverdue = $isOverdue
                ? (int) $dueDate->diff($today)->days
                : 0;

            $result[] = [
                'id' => $loan->getId(),
                'user_id' => $loan->getUser()->getId(),
                'book_id' => $loan->getBook()->getId(),
                'book_title' => $loan->getBook()->getTitle(),
                'borrowed_at' => $loan->getBorrowed()
                    ->format(DATE_ATOM),
                'due_at' => $loan->getDue_at()
                    ->format(DATE_ATOM),
                'returned_at' => $loan->getReturnedAt()
                    ?->format(DATE_ATOM),
                'status' => $isOverdue
                    ? 'overdue'
                    : $loan->getStatus(),
                'is_overdue' => $isOverdue,
                'days_overdue' => $daysOverdue,
            ];
        }

        return $result;
    }

    private function getSetting(string $key): string
    {
        $setting = $this->entityManager
            ->getRepository(SystemSetting::class)
            ->find($key);

        if ($setting === null) {
            throw new RuntimeException(
                'Missing system setting: ' . $key
            );
        }

        return $setting->getValue();
    }
}
