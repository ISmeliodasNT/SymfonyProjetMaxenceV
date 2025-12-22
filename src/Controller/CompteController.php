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
            'user' => $user
        ]);
    }

    #[Route('/adresse/ajouter', name: 'app_compte_adresse_new')]
    public function newAddress(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $address = new Address();
        
        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $address->setUser($this->getUser());

            $entityManager->persist($address);
            $entityManager->flush();

            // Cette clé manquait dans ton YAML, je l'ai ajoutée dans la réponse
            $this->addFlash('success', $translator->trans('texte_ajout_adresse_valide'));
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/address_form.html.twig', [
            'form' => $form->createView(),
            'titre' => $translator->trans('titre_ajout_adresse')
        ]);
    }

    #[Route('/adresse/modifier/{id}', name: 'app_compte_adresse_edit')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            // Traduction de l'exception
            throw $this->createAccessDeniedException($translator->trans('msg_erreur_acces_adresse'));
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('texte_modif_adresse_valide'));
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/address_form.html.twig', [
            'form' => $form->createView(),
            'titre' => $translator->trans('titre_modif_adresse')
        ]);
    }

    #[Route('/adresse/supprimer/{id}', name: 'app_compte_adresse_delete', methods: ['POST'])]
    public function deleteAddress(Address $address, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException($translator->trans('msg_erreur_action_non_autorisee'));
        }

        if ($this->isCsrfTokenValid('delete'.$address->getId(), $request->request->get('_token'))) {
            $entityManager->remove($address);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('texte_suppression_adresse_valide'));
        }

        return $this->redirectToRoute('app_compte');
    }

    #[Route('/carte/ajouter', name: 'app_compte_carte_new')]
    public function newCard(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $card = new CreditCard();
        $form = $this->createForm(CreditCardType::class, $card);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $card->setUser($this->getUser());

            $entityManager->persist($card);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('texte_ajout_carte_valide'));
            return $this->redirectToRoute('app_compte');
        }

        return $this->render('compte/card_form.html.twig', [
            'form' => $form->createView(),
            'titre' => $translator->trans('titre_ajout_carte')
        ]);
    }

    #[Route('/carte/supprimer/{id}', name: 'app_compte_carte_delete', methods: ['POST'])]
    public function deleteCard(CreditCard $card, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        if ($card->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException($translator->trans('msg_erreur_acces_carte'));
        }

        if ($this->isCsrfTokenValid('delete'.$card->getId(), $request->request->get('_token'))) {
            $entityManager->remove($card);
            $entityManager->flush();
            $this->addFlash('success', $translator->trans('texte_suppression_carte_valide'));
        }

        return $this->redirectToRoute('app_compte');
    }
}