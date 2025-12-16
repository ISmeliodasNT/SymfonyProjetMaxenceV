<?php

namespace App\Controller;

use App\Entity\Souris;
use App\Form\SourisType;
use App\Repository\SourisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/souris')]
final class SourisController extends AbstractController
{
    #[Route(name: 'app_souris_index', methods: ['GET'])]
    public function index(SourisRepository $sourisRepository): Response
    {
        return $this->render('souris/index.html.twig', [
            'souris' => $sourisRepository->findBy(['supprimeLe' => null]),
        ]);
    }

    #[Route('/new', name: 'app_souris_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $souri = new Souris();
        $form = $this->createForm(SourisType::class, $souri);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($souri);
            $entityManager->flush();

            return $this->redirectToRoute('app_souris_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('souris/new.html.twig', [
            'souri' => $souri,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_souris_show', methods: ['GET'])]
    public function show(Souris $souri): Response
    {
        return $this->render('souris/show.html.twig', [
            'souri' => $souri,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_souris_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Souris $souri, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SourisType::class, $souri);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_souris_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('souris/edit.html.twig', [
            'souri' => $souri,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_souris_delete', methods: ['POST'])]
    public function delete(Request $request, Souris $souri, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$souri->getId(), $request->request->get('_token'))) {
            
            $souri->setSupprimeLe(new \DateTimeImmutable());
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Le produit a été archivé avec succès.');
        }

        return $this->redirectToRoute('app_souris_index');
    }

    #[Route('/{id}/affichage', name: 'app_souris_affichage', methods: ['GET'])]
    public function affichage(int $id, SourisRepository $sourisRepository): Response
    {
        $souris = $sourisRepository->find($id);

        return $this->render('liste_produits/souris.html.twig', [
            'souris' => $souris,
        ]);
    }
}
