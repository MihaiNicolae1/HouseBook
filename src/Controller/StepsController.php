<?php

namespace App\Controller;
use DateTime;
use App\Entity\Steps;
use App\Form\StepsType;
use App\Repository\StageRepository;
use App\Repository\StepsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/steps')]
class StepsController extends AbstractController
{
    #[Route('/', name: 'steps_index', methods: ['GET'])]
    public function index(StepsRepository $stepsRepository): Response
    {
        return $this->render('steps/index.html.twig', [
            'steps' => $stepsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'steps_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, StepsRepository $stepsRepository, StageRepository $stageRepository): Response
    {
        $step = new Steps();

        $slug = $request->get('slug');
        $stage = $stageRepository->findOneBy(['slug' => $slug]);

        $form = $this->createForm(StepsType::class, $step);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //setting a unique slug to stage
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($step->getName())));
            $baseSlug  = $slug;// retaining the value of simple slugg
           
            //searching if there is a slug in database like this and while it is adding 1 to last character
            while($stepsRepository->findOneBy(['slug' => $slug])){ 
                $slug = $baseSlug ."-".$i++;       
            } 
            $step->setSlug($slug);

            $date = new DateTime();
            $step->setCreatedAt($date);

            $step->setStage($stage);

            $entityManager->persist($step);
            $entityManager->flush();

            return $this->redirectToRoute('steps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('steps/new.html.twig', [
            'step' => $step,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'steps_show', methods: ['GET'])]
    public function show(Steps $step): Response
    {
        return $this->render('steps/show.html.twig', [
            'step' => $step,
        ]);
    }

    #[Route('/{id}/edit', name: 'steps_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Steps $step, EntityManagerInterface $entityManager, StepsRepository $stepsRepository): Response
    {
        $form = $this->createForm(StepsType::class, $step);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //setting a unique slug to stage
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($step->getName())));
            $baseSlug  = $slug;// retaining the value of simple slugg
           
            //searching if there is a slug in database like this and while it is adding 1 to last character
            while($stepsRepository->findOneBy(['slug' => $slug])){ 
                $slug = $baseSlug ."-".$i++;       
            } 
            $stepsRepository->setSlug($slug);
            $entityManager->flush();

            return $this->redirectToRoute('steps_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('steps/edit.html.twig', [
            'step' => $step,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'steps_delete', methods: ['POST'])]
    public function delete(Request $request, Steps $step, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$step->getId(), $request->request->get('_token'))) {
            $entityManager->remove($step);
            $entityManager->flush();
        }

        return $this->redirectToRoute('steps_index', [], Response::HTTP_SEE_OTHER);
    }
}
