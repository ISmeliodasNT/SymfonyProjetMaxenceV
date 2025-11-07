<?php

namespace App\Entity;

use App\Repository\SourisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SourisRepository::class)]
class Souris extends Produit
{
    #[ORM\Column(length: 255)]
    private ?string $connectivite = null;

    #[ORM\Column(nullable: true)]
    private ?int $NbBoutons = null;

    public function getConnectivite(): ?string
    {
        return $this->connectivite;
    }

    public function setConnectivite(string $connectivite): static
    {
        $this->connectivite = $connectivite;

        return $this;
    }

    public function getNbBoutons(): ?int
    {
        return $this->NbBoutons;
    }

    public function setNbBoutons(?int $NbBoutons): static
    {
        $this->NbBoutons = $NbBoutons;

        return $this;
    }
}
