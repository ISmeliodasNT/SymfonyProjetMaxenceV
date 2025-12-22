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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin/commande')]
#[IsGranted('ROLE_ADMIN')]
class AdminCommandeController extends AbstractController
{
    #[Route('/', name: 'app_admin_commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin_commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['dateCommande' => 'DESC']),
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_commande_status', methods: ['POST'])]
    public function changeStatus(Commande $commande, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $newStatus = $request->request->get('etat');
        
        $allowedStatus = ['EN_ATTENTE', 'PAYEE', 'EXPEDIEE', 'LIVREE', 'ANNULEE'];

        if (in_array($newStatus, $allowedStatus)) {
            $commande->setEtat($newStatus);
            $em->flush();
            
            $statusTraduit = $translator->trans('status.' . $newStatus);

            $this->addFlash('success', $translator->trans('admin_commande_status_updated', [
                '%id%' => $commande->getId(),
                '%status%' => $statusTraduit
            ]));

        } else {
            $this->addFlash('danger', $translator->trans('admin_commande_status_invalid'));
        }

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