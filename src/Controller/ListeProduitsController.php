<?php

namespace App\Controller;

use App\Repository\SourisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ClavierRepository;

final class ListeProduitsController extends AbstractController
{
    #[Route('/liste/produits', name: 'app_liste_produits')]
    public function index(ClavierRepository $clavierRepository, SourisRepository $sourisRepository): Response
    {
        $claviers = $clavierRepository->findAll();
        $souris = $sourisRepository->findAll();

        return $this->render('liste_produits/index.html.twig', [
            'claviers' => $claviers,
            'souris' => $souris,
        ]);
    }
}
