<?php

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'penalties')]
class Penalty
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\ManyToOne(targetEntity: Loan::class)]
    #[ORM\JoinColumn(name: 'loan_id', referencedColumnName: 'id', nullable: false)]
    private Loan $loan;

    #[ORM\Column(type: 'decimal', precision:10, scale:2)]
    private string $amount;

    #[ORM\Column(type: 'integer')]
    private int $days_late;

    #[ORM\Column(type: 'boolean')]
    private bool $paid = false;

    #[ORM\Column(type: 'datetime', nullable:true)]
    private ?DateTime $paid_at = null;

    public function getLoan(): Loan{
        return $this->loan;
    }
    public function setLoan(Loan $loan): void{
        $this->loan = $loan;
    }

    public function getAmount(): string{
        return $this->amount;
    }
    public function setAmount(string $amount): void{
        if((float) $amount < 0){
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
        $this->amount = $amount;
    }

    public function getDaysLate(): int{
        return $this->days_late;
    }
    public function setDaysLate(int $days_late): void{
        if($days_late < 0){
            throw new \InvalidArgumentException('days late cannot be negative');
        }
        $this->days_late = $days_late;
    }

    public function isPaid():bool{
        return $this->paid;
    }
    public function setPaid(bool $paid): void{
        $this->paid = $paid;
    }

    public function getPaidAt(): ?DateTime{
        return $this->paid_at;
    }
    public function setPaidAt(?DateTime $paid_at): void{
        $this->paid_at = $paid_at;
    }
}