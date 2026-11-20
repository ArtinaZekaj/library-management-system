<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'system_settings')]
class SystemSetting
{
     /** @var string */
    #[ORM\Id]
    #[ORM\Column(type: 'string', length:255)]
    private string $key;

    /** @var string */
    #[ORM\Column(type: 'string', length:255)]
    private string $value;

    public function getKey():string{
        return $this->key;
    }
    public function setKey(string $key): void{
        $this->key = $key;
    }

    public function getValue():string{
        return $this->value;
    }
    public function setValue(string $value): void{
        $this->value = $value;
    }
}