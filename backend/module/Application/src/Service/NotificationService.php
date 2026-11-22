<?php

namespace Application\Service;

use Application\Entity\Book;
use Application\Entity\Loan;
use Application\Entity\Penalty;
use Application\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use DomainException;

class NotificationService
{
    public function __construct(
        private EntityManager $entityManager
    ) {}

    public function getNotifications(User $currentUser): array
    {
        $role = $currentUser->getRole();

        if (!in_array(
            $role,
            ['student', 'operator', 'super_admin'],
            true
        )) {
            throw new DomainException('Access denied', 403);
        }

        $notifications = [];

        if ($role === 'student') {
            $notifications = array_merge(
                $this->getOverdueBooks($currentUser),
                $this->getUnpaidPenalties($currentUser)
            );
        }

        if (in_array(
            $role,
            ['operator', 'super_admin'],
            true
        )) {
            $notifications = $this->getStockNotifications();
        }

        return $notifications;
    }

    private function getOverdueBooks(User $student): array
    {
        $today = (new DateTimeImmutable(
            'now',
            new DateTimeZone('UTC')
        ))->setTime(0, 0);

        $loans = $this->entityManager
            ->getRepository(Loan::class)
            ->findBy([
                'user' => $student,
                'returned_at' => null,
            ]);

        $notifications = [];

        foreach ($loans as $loan) {
            $dueDate = DateTimeImmutable::createFromMutable(
                $loan->getDue_at()
            )->setTimezone(
                new DateTimeZone('UTC')
            )->setTime(0, 0);

            if ($today > $dueDate) {
                $notifications[] = [
                    'type' => 'overdue_book',
                    'loan_id' => $loan->getId(),
                    'message' => 'Book is overdue',
                    'days_overdue' => (int) $dueDate
                        ->diff($today)->days,
                ];
            }
        }

        return $notifications;
    }

    private function getUnpaidPenalties(User $student): array
    {
        $penalties = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Penalty::class, 'p')
            ->join('p.loan', 'l')
            ->where('l.user = :student')
            ->andWhere('p.paid = :paid')
            ->setParameter('student', $student)
            ->setParameter('paid', false)
            ->getQuery()
            ->getResult();

        $notifications = [];

        foreach ($penalties as $penalty) {
            $notifications[] = [
                'type' => 'unpaid_penalty',
                'loan_id' => $penalty->getLoan()->getId(),     
                'amount' => $penalty->getAmount(),     
                'message' => 'You have an unpaid penalty',     
            ];
        }

        return $notifications;
    }

    private function getStockNotifications(): array
    {
        $books = $this->entityManager
            ->getRepository(Book::class)
            ->findBy([], ['stock' => 'ASC']);

        $notifications = [];

        foreach ($books as $book) {
            if ($book->getStock() > 2) {
                continue;
            }

            $outOfStock = $book->getStock() === 0;

            $notifications[] = [
                'type' => $outOfStock
                    ? 'out_of_stock'
                    : 'low_stock',
                'book_id' => $book->getId(),
                'book_title' => $book->getTitle(),
                'stock' => $book->getStock(),
                'message' => $outOfStock
                    ? 'Book is out of stock'
                    : 'Book stock is low',
            ];
        }

        return $notifications;
    }
}
