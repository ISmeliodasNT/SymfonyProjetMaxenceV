<?php

namespace App\Controller;

use App\Service\PanierService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panier')]
#[IsGranted('ROLE_USER')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index')]
    public function index(PanierService $panierService): Response
    {
        return $this->render('panier/index.html.twig', [
            'panier' => $panierService->obtenirPanierComplet(),
            'total' => $panierService->obtenirTotal()
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_panier_ajouter', methods: ['GET', 'POST'])]
    public function ajouter(int $id, Request $request, PanierService $panierService): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);
        
        // On tente d'ajouter et on récupère le résultat (vrai ou faux)
        $succes = $panierService->ajouter($id, $quantite);

        // SI C'EST DE L'AJAX (Stimulus)
        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
             
             // CAS D'ERREUR DE STOCK
             if (!$succes) {
                 // On renvoie un code 400 (Bad Request) avec un message
                 return new JsonResponse([
                     'erreur' => 'Stock insuffisant ! Vous ne pouvez pas ajouter plus de produits que ce que nous avons en réserve.'
                 ], 400); 
             }

             // CAS DE SUCCÈS (Code existant inchangé)
             $panier = $panierService->obtenirPanierComplet();
             $ligneActuelle = null;
             foreach($panier as $item) {
                 if($item['produit']->getId() === $id) {
                     $ligneActuelle = $item;
                     break;
                 }
             }

             return new JsonResponse([
                 'nouvelleQuantite' => $ligneActuelle ? $ligneActuelle['quantite'] : 0,
                 'nouveauTotalLigne' => $ligneActuelle ? number_format($ligneActuelle['totalLigne'], 2, ',', ' ') : '0,00',
                 'nouveauTotalGlobal' => number_format($panierService->obtenirTotal(), 2, ',', ' '),
                 'compteurPanier' => count($panier)
             ]);
        }

        // SI CE N'EST PAS DE L'AJAX (Redirection classique)
        if (!$succes) {
            $this->addFlash('danger', 'Stock insuffisant pour cet article.');
        }

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/diminuer/{id}', name: 'app_panier_diminuer')]
    public function diminuer(int $id, Request $request, PanierService $panierService): Response
    {
        $panierService->diminuer($id);

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
             $panier = $panierService->obtenirPanierComplet();
             $ligneActuelle = null;
             foreach($panier as $item) {
                 if($item['produit']->getId() === $id) {
                     $ligneActuelle = $item;
                     break;
                 }
             }

             return new JsonResponse([
                 'supprime' => $ligneActuelle === null, // Dit au JS si la ligne a disparu (qte = 0)
                 'nouvelleQuantite' => $ligneActuelle ? $ligneActuelle['quantite'] : 0,
                 'nouveauTotalLigne' => $ligneActuelle ? number_format($ligneActuelle['totalLigne'], 2, ',', ' ') : '0,00',
                 'nouveauTotalGlobal' => number_format($panierService->obtenirTotal(), 2, ',', ' '),
             ]);
        }

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/supprimer/{id}', name: 'app_panier_supprimer')]
    public function supprimer(int $id, Request $request, PanierService $panierService): Response
    {
        $panierService->supprimer($id);

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return new JsonResponse([
                'supprime' => true,
                'nouveauTotalGlobal' => number_format($panierService->obtenirTotal(), 2, ',', ' ')
            ]);
        }

        return $this->redirectToRoute('app_panier_index');
    }
}