<?php

namespace App\Entity;

use App\Repository\ClavierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClavierRepository::class)]
class Clavier extends Produit
{
    #[ORM\Column(length: 255)]
    private ?string $switch = null;

    #[ORM\Column(length: 255)]
    private ?string $language = null;

    public function getSwitch(): ?string
    {
        return $this->switch;
    }

    public function setSwitch(string $switch): static
    {
        $this->switch = $switch;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }
}
