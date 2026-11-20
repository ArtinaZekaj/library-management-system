<?php

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'loans')]
class Loan
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(name: 'book_id', referencedColumnName: 'id', nullable: false)]
    private Book $book;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $borrowed_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $due_at;

    #[ORM\Column(type: 'datetime', nullable:true)]
    private ?DateTime $returned_at = null;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $status='active';

    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getBook(): Book
    {
        return $this->book;
    }
    public function setBook(Book $book): void
    {
        $this->book = $book;
    }


    public function __construct(){
        $this->borrowed_at = new DateTime();
    }
    public function getBorrowed(): DateTime{
        return $this->borrowed_at;
    }

    public function getDue_at(): DateTime{
        return $this->due_at;
    }
    public function setDue_at(DateTime $due_at): void
    {
        $this->due_at = $due_at;
    }

    public function getReturnedAt(): ?DateTime{
        return $this->returned_at;
    }
    public function setReturnedAt(?DateTime $returned_at): void
    {
        $this->returned_at = $returned_at;
    }

    public function setStatus(string $status): void{
       $allowed = ['active', 'overdue', 'awaiting_payment', 'closed'];

       if (!in_array($status, $allowed, true)){
        throw new \InvalidArgumentException('Invalid status');
       }

       $this->status = $status;
    }
    public function getStatus(): string{
        return $this->status;
    }
}