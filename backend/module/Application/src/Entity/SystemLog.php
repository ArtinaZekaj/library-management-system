<?php

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'system_logs')]
class SystemLog
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id', nullable: false)]
    private User $actor;

     /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $action_type;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $entity_type;

    #[ORM\Column(type: 'integer')]
    private int $entity_id;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $timestamp;

    #[ORM\Column(type: 'text', nullable:true)]
    private ?string $details=null;

    public function __construct(){
        $this->timestamp = new DateTime();
    }

    public function getActor(): User{
        return $this->actor;
    }
    public function setActor(User $actor): void{
        $this->actor = $actor;
    }

    public function getActionType(): string{
        return $this->action_type;
    }
    public function setActionType(string $action_type): void{
        $this->action_type = $action_type;
    }

    public function getEntityType(): string{
        return $this->entity_type;
    }
    public function setEntityType(string $entity_type): void{
        $this->entity_type = $entity_type;
    }

    public function getEntityId(): int{
        return $this->entity_id;
    }
    public function setEntityId(int $entity_id): void{
        $this->entity_id = $entity_id;
    }

    public function getTimestamp(): DateTime{
        return $this->timestamp;
    }

    public function getDetails(): ?string{
        return $this->details;
    }
    public function setDetails(?string $details): void{
        $this->details = $details;
    }
}