<?php

namespace App\Controller;

use App\Entity\Cost;
use App\Entity\CursBNR;
use App\Form\CostType;
use App\Repository\CostRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cost')]
class CostController extends AbstractController
{
    #[Route('/', name: 'cost_index', methods: ['GET'])]
    public function index(CostRepository $costRepository): Response
    {
        return $this->render('cost/index.html.twig', [
            'costs' => $costRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'cost_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): Response
    {

        $slug = $request->get('slug');
        $project = $projectRepository->findOneBy(['slug' => $slug]);
        $curs = new CursBNR("https://www.bnr.ro/nbrfxrates.xml");

        $cost = new Cost();
        $form = $this->createForm(CostType::class, $cost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $currencyChoice = $form->get("currencyChoice")->getData();

            if($currencyChoice == "EUR"){
                $eurValue = $form->get("value")->getData();

                $cost->setEur($eurValue);
                $cost->setUsd(round($eurValue/1.1),2);
                $cost->setRon($eurValue*4.89);
            }






            $cost->setProject($project);
            $entityManager->persist($cost);
            $entityManager->flush();

            return $this->redirectToRoute('cost_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cost/new.html.twig', [
            'cost' => $cost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'cost_show', methods: ['GET'])]
    public function show(Cost $cost): Response
    {
        return $this->render('cost/show.html.twig', [
            'cost' => $cost,
        ]);
    }

    #[Route('/{id}/edit', name: 'cost_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cost $cost, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CostType::class, $cost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('cost_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cost/edit.html.twig', [
            'cost' => $cost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'cost_delete', methods: ['POST'])]
    public function delete(Request $request, Cost $cost, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cost);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cost_index', [], Response::HTTP_SEE_OTHER);
    }
}
