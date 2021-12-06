<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Stage;
use App\Form\StageType;
use App\Repository\StageRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Name;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage')]
class StageController extends AbstractController
{
    #[Route('/', name: 'stage_index', methods: ['GET'])]
    public function index(StageRepository $stageRepository): Response
    {
        return $this->render('stage/index.html.twig', [
            'stages' => $stageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'stage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,ProjectRepository $projectRepository): Response
    {
        $stage = new Stage();
        $form = $this->createForm(StageType::class, $stage);
        $form->handleRequest($request);
        // should modify something here

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($stage);
            $entityManager->flush();

            return $this->redirectToRoute('stage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('stage/new.html.twig', [
            'stage' => $stage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'stage_show', methods: ['GET'])]
    public function show(Stage $stage): Response
    {
        return $this->render('stage/show.html.twig', [
            'stage' => $stage,
        ]);
    }

    #[Route('/{id}/edit', name: 'stage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stage $stage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StageType::class, $stage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('stage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('stage/edit.html.twig', [
            'stage' => $stage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'stage_delete', methods: ['POST'])]
    public function delete(Request $request, Stage $stage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stage->getId(), $request->request->get('_token'))) {
            $entityManager->remove($stage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('stage_index', [], Response::HTTP_SEE_OTHER);
    }
}
