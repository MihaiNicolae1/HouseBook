<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Project;
use App\Form\DocumentType;
use App\Repository\StepsRepository;
use DateTime;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/document')]
class DocumentController extends AbstractController
{
    #[Route('/{project}', name: 'document_index', methods: ['GET'])]
    public function index(DocumentRepository $documentRepository, Request $request, ProjectRepository $projectRepository): Response
    {

        $project_slug = $request->get('project');
        $project = $projectRepository->findOneBy(['slug' => $project_slug]);
        $project_id = $project->getId();

        return $this->render('document/index.html.twig', [
            'documents' => $documentRepository->findBy(['project_id' => $project_id])
        ]);
    }

    #[Route('/{slug}/new', name: 'document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProjectRepository $projectRepository, StepsRepository $stepsRepository, SluggerInterface $slugger): Response
    {
        $project_slug = $request->get('slug');
        $step_slug = $request->get('step');

        if ($step_slug != null){
            $step = $stepsRepository->findOneBy(['slug' => $step_slug]);
        } else {
            $step = null;
        }
        $project = $projectRepository->findOneBy(['slug' => $project_slug]);

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        
        

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->get('security.token_storage')->getToken()->getUser();//getting the current user
            $document->setAuthor($user); // setting the current user to be the author

            $document->setProjectId($project);
            $document->setStep($step);

            $file_location = $this->getParameter('projects_directory') . '/' . $project->getProjectDirectory();

            $file = $form->get('file')->getData();//getting the file from form
            if ($file){
                //getting the filename
                $originalFile = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);

                $safeFile = $slugger->slug($originalFile).'.'.$file->guessExtension();

                $newNameFile = $safeFile.'-'.uniqid().'.'.$file->guessExtension();
                //Moving the file to the directory where documents are stored
                try{
                    $file->move($file_location,$newNameFile);
                }
                catch(FileException $exception){
                    // exceptions
                    echo $exception;
                }
                $document->setName($safeFile);
                $document->setPath($newNameFile);
            }

            $date = new DateTime();
            $document->setCreatedAt($date);

            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('project_show', ["slug"=>$project_slug], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('document/new.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/edit', name: 'document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('document/edit.html.twig', [
            'document' => $document,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager, ProjectRepository $projectRepository): Response
    {
        $projectId = $document->getProjectId();
        $project = $projectRepository->findOneBy(['id'=>$projectId]);
        $projectSlug = $project->getSlug();
        if ($this->isCsrfTokenValid('delete'.$document->getId(), $request->request->get('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();
        }

        return $this->redirectToRoute('document_index', ['project'=>$projectSlug], Response::HTTP_SEE_OTHER);
    }

    #[Route('/download/{file}', name: 'document_download', methods: ['GET'])]
    public function download(Request $request, DocumentRepository $documentRepository)
    {

        $filePath = $request->get('file');
        $document = $documentRepository->findOneBy(['path' => $filePath]);


        if($document){

            $fileName = $document->getName();

            $project = $document->getProjectId();
            $project_directory = $project->getProjectDirectory();


            $fileDownload = $this->getParameter('projects_directory') . '/' . $project_directory . '/' . $filePath;

            // define header
            header("Cache-control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$fileName");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            readfile($fileDownload);
            exit;
        }
        else{
            return $this->redirectToRoute('project_show',[],Response::HTTP_SEE_OTHER);
        }
    }


}
