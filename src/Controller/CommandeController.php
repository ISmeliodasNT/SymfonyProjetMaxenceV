<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\DetailCommande;
use App\Form\CheckoutType;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Utilisateur;
use App\Repository\CommandeRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class CommandeController extends AbstractController
{

    #[Route('/', name: 'app_commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy(
                ['user' => $this->getUser()],
                ['dateCommande' => 'DESC']
            ),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/recapitulatif', name: 'app_commande_recap')]
    public function recap(PanierService $panierService, Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $panier = $panierService->obtenirPanierComplet();
        if (empty($panier)) {
            $this->addFlash('warning', $translator->trans('texte_warning_panier_vide'));
            return $this->redirectToRoute('app_liste_produits');
        }
        
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            throw new \LogicException($translator->trans('texte_warning_utilisateur_invalide'));
        }
        
        if ($user->getAdresses()->isEmpty() || $user->getCreditCards()->isEmpty()) {
            $this->addFlash('warning', $translator->trans('texte_warning_aucun_adresse_cb'));
            return $this->redirectToRoute('app_compte');
        }

        $form = $this->createForm(CheckoutType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            foreach ($panier as $item) {
                /** @var \App\Entity\Produit $produit */
                $produit = $item['produit'];
                
                if ($produit->getStock() < $item['quantite']) {
                    $this->addFlash('danger', $translator->trans('erreur_stock_insuffisant', [
                        '%produit%' => $produit->getNom(),
                        '%count%' => $produit->getStock()
                    ]));
                    return $this->redirectToRoute('app_panier_index');
                }
            }

            $data = $form->getData();
            $date = new \DateTimeImmutable();

            $commande = new Commande();
            $commande->setUser($user);
            $commande->setDateCommande($date);
            $commande->setEtat('EN_ATTENTE');
            $commande->setTotal($panierService->obtenirTotal());
            $commande->setAdresseLivraison($data['adresseLivraison']);
            $commande->setCartePaiement($data['cartePaiement']);

            $em->persist($commande);

            foreach ($panier as $item) {
                $detail = new DetailCommande();
                $detail->setCommande($commande);
                $detail->setProduit($item['produit']);
                $detail->setQuantite($item['quantite']);
                $detail->setPrixUnitaire($item['produit']->getPrix());
                $em->persist($detail);

                $produit = $item['produit'];
                $nouveauStock = $produit->getStock() - $item['quantite'];
                $produit->setStock($nouveauStock);
                
            }

            $em->flush();

            $panierService->vider();

            return $this->redirectToRoute('app_commande_success', ['id' => $commande->getId()]);
        }

        return $this->render('commande/recap.html.twig', [
            'panier' => $panier,
            'total' => $panierService->obtenirTotal(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/succes/{id}', name: 'app_commande_success')]
    public function success(Commande $commande): Response
    {
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/success.html.twig', [
            'commande' => $commande
        ]);
    }
}