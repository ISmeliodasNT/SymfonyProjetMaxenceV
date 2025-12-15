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
        $derniersProduits = $produitRepository->findBy([], ['id' => 'DESC'], 5);

        return $this->render('accueil.html.twig', [
            'products' => $derniersProduits,
        ]);
    }
}