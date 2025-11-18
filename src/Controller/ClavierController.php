<?php

namespace App\Controller;

use App\Entity\Clavier;
use App\Form\ClavierType;
use App\Repository\ClavierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clavier')]
final class ClavierController extends AbstractController
{
    #[Route(name: 'app_clavier_index', methods: ['GET'])]
    public function index(ClavierRepository $clavierRepository): Response
    {
        return $this->render('clavier/index.html.twig', [
            'claviers' => $clavierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_clavier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $clavier = new Clavier();
        $form = $this->createForm(ClavierType::class, $clavier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($clavier);
            $entityManager->flush();

            return $this->redirectToRoute('app_clavier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clavier/new.html.twig', [
            'clavier' => $clavier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clavier_show', methods: ['GET'])]
    public function show(Clavier $clavier): Response
    {
        return $this->render('clavier/show.html.twig', [
            'clavier' => $clavier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_clavier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Clavier $clavier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClavierType::class, $clavier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_clavier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('clavier/edit.html.twig', [
            'clavier' => $clavier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_clavier_delete', methods: ['POST'])]
    public function delete(Request $request, Clavier $clavier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$clavier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($clavier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_clavier_index', [], Response::HTTP_SEE_OTHER);
    }
}
