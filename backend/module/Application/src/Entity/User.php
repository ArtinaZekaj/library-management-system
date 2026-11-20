<?php
namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $name;

    /** @var string */
    #[ORM\Column(type: 'string', unique:true, length:255)]
    private string $email;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $password_hash;

   /** @var string */
    #[ORM\Column(type: 'string', length:50)]
    private string $role;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;


    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string{
        return $this->email;
    }
    public function setEmail(string $email): void{
       $this->email = $email;
    }

    public function setPasswordHash(string $passwordHash): void{
        $this->password_hash = $passwordHash;
    }
    public function getPasswordHash(): string{
        return $this->password_hash;
    }

    public function getRole(): string{
        return $this->role;
    }
    public function setRole(string $role): void{
       $allowed = ['student', 'operator', 'super_admin'];

       if (!in_array($role, $allowed, true)){
        throw new \InvalidArgumentException('Invalid role');
       }

       $this->role = $role;
    }

    public function __construct(){
        $this->created_at = new DateTime();
    }
    public function getCreatedAt(): DateTime{
        return $this->created_at;
    }
    
}