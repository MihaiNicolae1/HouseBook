<?php

namespace App\Controller;

use App\Entity\Project;
use DateTime;
use App\Form\ProjectType;
use App\Services\createSlug;
use App\Services\addFile;
use App\Repository\ProjectRepository;
use App\Repository\StageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


use function App\Services\createSlug;

#[Route('/project')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'project_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository,UserInterface $user): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();//getting the current user
        $projects = $projectRepository->findBy(['user'=>$user]);

        return $this->render('project/index.html.twig', [
            'projects' =>  $projects,
        ]);
        
    }

    #[Route('/new', name: 'project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserInterface $user,ProjectRepository $projectRepository, SluggerInterface $slugger): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        
        $user = $this->get('security.token_storage')->getToken()->getUser();//getting the current user
        $project->setUser($user); // setting the current user to the project

        if ($form->isSubmitted() && $form->isValid()) {

            $slug = createSlug($projectRepository,$project);
            

            //Creating unique value for project directory
            $project_directory ='project_' . uniqid();
            //Setting the value
            $project->setProjectDirectory($project_directory);


            //Getting the location and creating the new folder
            $file_location = $this->getParameter('projects_directory') . '/' . $project_directory;
            mkdir($file_location, 0700);


            $profilePicture = $form->get('ProfilePicture')->getData();//getting the profile picture
            

            if ($profilePicture){
                //getting the filename
                $originalPicture = pathinfo($profilePicture->getClientOriginalName(),PATHINFO_FILENAME);
                $safePicture = $slugger->slug($originalPicture);
                $newNamePicture = $safePicture.'-'.uniqid().'.'.$profilePicture->guessExtension();
                //Moving the picture to the directory where profile pictures are stored
                try{
                    $profilePicture->move($file_location,$newNamePicture);
                }
                catch(FileException $exception){
                    // exceptions
                    echo $exception;
                }
                $project->setProfilePicture($newNamePicture);
            }
            
            $project->setSlug($slug);
            $date = new DateTime();
            $project->setCreatedAt($date);
            $project->setCostEuro(0);
            $project->setCostUsd(0);
            $project->setCostRon(0);

            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/show/{slug}', name: 'project_show', methods: ['GET'])]
    public function show(Project $project, StageRepository $stageRepository): Response
    {
        
        
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/edit/{slug}', name: 'project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //setting a unique slug to project
            $i=1;
            
            $slug = preg_replace('/[^a-z0-9]+/i','-',trim(strtolower($project->getName())));
            $baseSlug  = $slug;// retaining the value of simple slug
           
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

    #[Route('/delete/{slug}/', name: 'project_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getSlug(), $request->request->get('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('project_index', [], Response::HTTP_SEE_OTHER);
    }
}
