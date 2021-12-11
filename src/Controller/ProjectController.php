<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Stage;
use App\Form\ProjectType;
use App\Form\StageType;
use App\Repository\ProjectRepository;
use App\Repository\StageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        return $this->render('project/index.html.twig', [
            'projects' => $projectRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserInterface $user,ProjectRepository $projectRepository): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        
        $user = $this->get('security.token_storage')->getToken()->getUser();//getting the current user
        $project->setUser($user); // setting the current user to the project

        if ($form->isSubmitted() && $form->isValid()) {

        

            //setting a unique slug to stage
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($project->getName())));
            $baseSlug  = $slug;// retaining the value of simple slugg
           
            //searching if there is a slug in database like this and while it is adding 1 to last character
            while($projectRepository->findOneBy(['slug' => $slug])){ 
                $slug = $baseSlug ."-".$i++;       
            } 


            $project->setSlug($slug);
            
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project, StageRepository $stageRepository): Response
    {
        
        
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{slug}/edit', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //setting a unique slug to stage
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($project->getName())));
            $baseSlug  = $slug;// retaining the value of simple slugg
           
            //searching if there is a slug in database like this and while it is adding 1 to last character
            while($projectRepository->findOneBy(['slug' => $slug])){ 
                $slug = $baseSlug ."-".$i++;       
            } 


            $project->setSlug($slug);
            $entityManager->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
    }
}