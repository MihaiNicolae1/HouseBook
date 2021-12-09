<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Stage;
use App\Form\StageType;
use App\Repository\ProjectRepository;
use App\Repository\StageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

$i = 1;
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
    public function new(Request $request, EntityManagerInterface $entityManager,StageRepository $stageRepository, ProjectRepository $projectRepository): Response
    {
        
        $slug = $request->get('slug');
        $project = $projectRepository->findOneBy(['slug' => $slug]);
       
        $stage = new Stage();

        
        $form = $this->createForm(StageType::class, $stage);
        $form->handleRequest($request);

        
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            //setting a unique slug to stage
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($stage->getName())));
            $baseSlug  = $slug;// retaining the value of simple slugg
           
            //searching if there is a slug in database like this and while it is adding 1 to last character
            while($stageRepository->findOneBy(['slug' => $slug])){ 
                $slug = $baseSlug ."-".$i++;       
            } 

            $stage->setProject($project);
            $stage->setSlug($slug);

            $entityManager->persist($stage);
            $entityManager->flush();

            return $this->redirectToRoute('stage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('stage/new.html.twig', [
            'stage' => $stage,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'stage_show', methods: ['GET'])]
    public function show(Stage $stage): Response
    {
        return $this->render('stage/show.html.twig', [
            'stage' => $stage,
        ]);
    }

    #[Route('/{slug}/edit', name: 'stage_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{slug}', name: 'stage_delete', methods: ['POST'])]
    public function delete(Request $request, Stage $stage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$stage->getId(), $request->request->get('_token'))) {
            $entityManager->remove($stage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('stage_index', [], Response::HTTP_SEE_OTHER);
    }
}
