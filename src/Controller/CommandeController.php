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

#[Route('/commande')]
#[IsGranted('ROLE_USER')]
class CommandeController extends AbstractController
{

    #[Route('/', name: 'app_commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            // On récupère les commandes de l'utilisateur connecté, triées par date décroissante
            'commandes' => $commandeRepository->findBy(
                ['user' => $this->getUser()],
                ['dateCommande' => 'DESC']
            ),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Commande $commande): Response
    {
        // Sécurité : on ne peut voir que ses propres commandes
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/recapitulatif', name: 'app_commande_recap')]
    public function recap(PanierService $panierService, Request $request, EntityManagerInterface $em): Response
    {
        // 1. On vérifie que le panier n'est pas vide
        $panier = $panierService->obtenirPanierComplet();
        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_liste_produits');
        }

        // 2. Préparation de l'utilisateur et du formulaire
        
        $user = $this->getUser();

        if (!$user instanceof Utilisateur) {
            throw new \LogicException('L\'utilisateur connecté n\'est pas valide.');
        }
        
        // Si l'utilisateur n'a pas d'adresse ou de carte, on le redirige vers son compte
        if ($user->getAdresses()->isEmpty() || $user->getCreditCards()->isEmpty()) {
            $this->addFlash('warning', 'Vous devez ajouter une adresse et une carte de paiement avant de commander.');
            return $this->redirectToRoute('app_compte');
        }

        $form = $this->createForm(CheckoutType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            // 🛑 ÉTAPE 1 : VÉRIFICATION DE SÉCURITÉ (ULTIME)
            // On revérifie le stock une dernière fois avant de valider
            foreach ($panier as $item) {
                /** @var \App\Entity\Produit $produit */
                $produit = $item['produit'];
                
                if ($produit->getStock() < $item['quantite']) {
                    $this->addFlash('danger', sprintf(
                        'Désolé, le stock de "%s" a changé. Il ne reste que %d exemplaire(s).',
                        $produit->getNom(),
                        $produit->getStock()
                    ));
                    return $this->redirectToRoute('app_panier_index');
                }
            }

            // SI TOUT EST OK, ON LANCE LA COMMANDE
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

            // 📉 ÉTAPE 2 : SOUSTRACTION DU STOCK
            foreach ($panier as $item) {
                // Création de la ligne de commande
                $detail = new DetailCommande();
                $detail->setCommande($commande);
                $detail->setProduit($item['produit']);
                $detail->setQuantite($item['quantite']);
                $detail->setPrixUnitaire($item['produit']->getPrix());
                $em->persist($detail);

                // 👇 C'EST ICI QUE LA MAGIE OPÈRE 👇
                $produit = $item['produit'];
                $nouveauStock = $produit->getStock() - $item['quantite'];
                $produit->setStock($nouveauStock);
                
                // Note : Pas besoin de faire $em->persist($produit) car l'objet 
                // existe déjà. Le $em->flush() plus bas va détecter 
                // le changement de stock et le sauvegarder automatiquement.
            }

            $em->flush(); // Sauvegarde TOUT (Commande, Détails ET Stocks modifiés)

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
        // Sécurité : on ne voit que ses propres commandes
        if ($commande->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/success.html.twig', [
            'commande' => $commande
        ]);
    }
}