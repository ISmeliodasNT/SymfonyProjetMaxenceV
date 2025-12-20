<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commande')]
#[IsGranted('ROLE_ADMIN')]
class AdminCommandeController extends AbstractController
{
    #[Route('/', name: 'app_admin_commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin_commande/index.html.twig', [
            // On affiche toutes les commandes, de la plus récente à la plus ancienne
            'commandes' => $commandeRepository->findBy([], ['dateCommande' => 'DESC']),
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_commande_status', methods: ['POST'])]
    public function changeStatus(Commande $commande, Request $request, EntityManagerInterface $em): Response
    {
        // On récupère le nouveau statut depuis le formulaire
        $newStatus = $request->request->get('etat');
        
        // Liste des statuts autorisés (sécurité)
        $allowedStatus = ['EN_ATTENTE', 'PAYEE', 'EXPEDIEE', 'LIVREE', 'ANNULEE'];

        if (in_array($newStatus, $allowedStatus)) {
            $commande->setEtat($newStatus);
            $em->flush();
            $this->addFlash('success', 'Statut de la commande #' . $commande->getId() . ' mis à jour.');
        } else {
            $this->addFlash('danger', 'Statut invalide.');
        }

        // On reste sur la même page
        return $this->redirectToRoute('app_admin_commande_index');
    }

    #[Route('/{id}', name: 'app_admin_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('admin_commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }
}