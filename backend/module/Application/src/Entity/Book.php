<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'books')]
class Book
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $title;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $author;

    /** @var string */
    #[ORM\Column(type: 'string', length:255, unique:true)]
    private string $isbn;

   /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $category;

    /** @var int */
    #[ORM\Column(type: 'integer')]
    private int $stock=0;


    public function getId(): int|null
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAuthor(): string{
        return $this->author;
    }
    public function setAuthor(string $author): void{
       $this->author = $author;
    }

    
    public function getIsbn(): string{
        return $this->isbn;
    }
    public function setIsbn(string $isbn): void{
        $this->isbn = $isbn;
    }

    public function getCategory(): string{
        return $this->category;
    }
    public function setCategory(string $category): void{
        $this->category = $category;
    }

    public function getStock(): int{
        return $this->stock;
    }
    public function setStock(int $stock): void{
        if($stock < 0){
            throw new \InvalidArgumentException('Invalid stock');
        }
        $this->stock = $stock;
    }

    public function getStatus():string{
        if($this->stock > 0){
            return 'available';
        }
        return 'unavailable';
    }
    
}