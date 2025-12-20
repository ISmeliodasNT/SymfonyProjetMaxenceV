<?php

namespace App\Controller;

use App\Entity\Address;
use App\Form\AddressType;
use App\Form\CompteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\CreditCard;
use App\Form\CreditCardType;

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
class CompteController extends AbstractController
{
    // --- 1. VOTRE MÉTHODE EXISTANTE (PROFIL) ---
    #[Route('/', name: 'app_compte')]
    public function index(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(CompteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('texte_modif_compte_valide'));
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user // J'ajoute user pour pouvoir afficher la liste des adresses dans la vue
        ]);
    }

    // --- 2. NOUVELLES MÉTHODES (GESTION ADRESSES) ---

    #[Route('/adresse/ajouter', name: 'app_compte_adresse_new')]
    public function newAddress(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $address = new Address();
        
        // On crée le formulaire AddressType
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // IMPORTANT : On relie l'adresse à l'utilisateur connecté
            $address->setUser($this->getUser());

            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Votre adresse a été ajoutée.');
            return $this->redirectToRoute('app_compte');
        }

        // On affiche une vue spécifique pour le formulaire d'adresse
        // Notez que je mets le fichier dans le dossier 'compte' pour rester cohérent
        return $this->render('compte/address_form.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Ajouter une adresse'
        ]);
    }

    #[Route('/adresse/modifier/{id}', name: 'app_compte_adresse_edit')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        // SÉCURITÉ : On empêche de modifier l'adresse d'un autre utilisateur
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit de modifier cette adresse.");
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Adresse modifiée avec succès.');
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/address_form.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Modifier l\'adresse'
        ]);
    }

    #[Route('/adresse/supprimer/{id}', name: 'app_compte_adresse_delete', methods: ['POST'])]
    public function deleteAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        // SÉCURITÉ
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Action non autorisée.");
        }

        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->request->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('success', 'Adresse supprimée.');
        }

        return $this->redirectToRoute('app_compte');
    }

    #[Route('/carte/ajouter', name: 'app_compte_carte_new')]
    public function newCard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $card = new CreditCard();
        $form = $this->createForm(CreditCardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $card->setUser($this->getUser()); // On lie au user connecté

            $entityManager->persist($card);
            $entityManager->flush();

            $this->addFlash('success', 'Carte de paiement ajoutée.');
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/card_form.html.twig', [
            'form' => $form->createView(),
            'titre' => 'Ajouter une carte'
        ]);
    }

    #[Route('/carte/supprimer/{id}', name: 'app_compte_carte_delete', methods: ['POST'])]
    public function deleteCard(CreditCard $card, Request $request, EntityManagerInterface $entityManager): Response
    {
        // SÉCURITÉ : Vérifier que la carte appartient bien au user
        if ($card->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Cette carte ne vous appartient pas.");
        }

        if ($this->isCsrfTokenValid('delete'.$card->getId(), $request->request->get('_token'))) {
            $entityManager->remove($card);
            $entityManager->flush();
            $this->addFlash('success', 'Carte supprimée.');
        }

        return $this->redirectToRoute('app_compte');
    }
}