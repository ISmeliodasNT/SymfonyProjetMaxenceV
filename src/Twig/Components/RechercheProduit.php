<?php

namespace App\Twig\Components;

use App\Repository\ProduitRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class RechercheProduit
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(private ProduitRepository $produitRepository)
    {
    }

    public function getProducts(): array
    {
        if (empty($this->query)) {
            return $this->produitRepository->findBy([], ['id' => 'DESC']);
        }
        return $this->produitRepository->findByName($this->query);
    }
}