<?php

namespace App\Service;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class PanierService
{
    private $requestStack;
    private $produitRepository;

    public function __construct(RequestStack $requestStack, ProduitRepository $produitRepository)
    {
        $this->requestStack = $requestStack;
        $this->produitRepository = $produitRepository;
    }

    public function ajouter(int $id, int $quantite = 1): bool
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            return false; 
        }

        $quantiteActuelle = $panier[$id] ?? 0;
        $quantiteFuture = $quantiteActuelle + $quantite;

        if ($quantiteFuture > $produit->getStock()) {
            return false; 
        }

        $panier[$id] = $quantiteFuture;
        $session->set('panier', $panier);

        return true; 
    }

    public function diminuer(int $id): void
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);
    }

    public function supprimer(int $id): void
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);
    }

    public function vider(): void
    {
        $this->requestStack->getSession()->remove('panier');
    }

    public function obtenirPanierComplet(): array
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);
        $donneesPanier = [];

        foreach ($panier as $id => $quantite) {
            $produit = $this->produitRepository->find($id);

            if ($produit && !$produit->isSupprimeLe()) {
                $donneesPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'totalLigne' => $produit->getPrix() * $quantite
                ];
            } else {

                $this->supprimer($id);
            }
        }

        return $donneesPanier;
    }

    public function obtenirTotal(): float
    {
        $panierComplet = $this->obtenirPanierComplet();
        $total = 0;

        foreach ($panierComplet as $item) {
            $total += $item['totalLigne'];
        }

        return $total;
    }
}