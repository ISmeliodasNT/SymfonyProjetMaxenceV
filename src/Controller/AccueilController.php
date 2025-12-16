<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(ProduitRepository $produitRepository): Response
    {
        // 👇 MODIFICATION ICI : On ajoute le critère ['deletedAt' => null]
        $derniersProduits = $produitRepository->findBy(
            ['supprimeLe' => null], // Critère : Seulement ceux qui ne sont PAS supprimés
            ['id' => 'DESC'],      // Tri : Du plus récent au plus ancien
            5                      // Limite : 5 résultats
        );

        return $this->render('accueil.html.twig', [
            'products' => $derniersProduits,
        ]);
    }
}