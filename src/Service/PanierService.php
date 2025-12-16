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

    public function ajouter(int $id, int $quantite = 1): bool // <-- On change le type de retour
    {
        $session = $this->requestStack->getSession();
        $panier = $session->get('panier', []);

        // 1. On récupère le produit pour connaître son stock réel
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            return false; // Produit n'existe pas
        }

        // 2. On calcule la quantité future
        $quantiteActuelle = $panier[$id] ?? 0;
        $quantiteFuture = $quantiteActuelle + $quantite;

        // 3. VÉRIFICATION DU STOCK
        if ($quantiteFuture > $produit->getStock()) {
            return false; // Pas assez de stock ! On arrête tout.
        }

        // 4. Si c'est bon, on sauvegarde
        $panier[$id] = $quantiteFuture;
        $session->set('panier', $panier);

        return true; // Succès
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

            // On vérifie si le produit existe ET s'il n'est pas supprimé
            if ($produit && !$produit->isSupprimeLe()) {
                $donneesPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'totalLigne' => $produit->getPrix() * $quantite
                ];
            } else {
                // OPTIONNEL : Si le produit a été supprimé entre temps,
                // on le retire automatiquement du panier de l'utilisateur pour nettoyer
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