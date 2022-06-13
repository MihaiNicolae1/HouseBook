<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{
    #[Route('/chart', name: 'chart_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $projects = $projectRepository->findBy(['user' => $user]);
        foreach ($projects as $project){
            $projectNames[] = $project->getName();
            $projectColors[] = 'rgb('. rand(0,256) . ',' . rand(0,256) . ',' . rand(0,256). ', 0.6)';
            $projectCosts[] = $project->getCostEuro();
            $projectDocs[] = count($project->getDocuments());

            $stages = $project->getStage();
            $stepCounter = 0;
            foreach ($stages as $stage){
                $stepCounter += count($stage->getSteps());
            }
            $projectSteps[] = $stepCounter;
        }

        return $this->render('chart/index.html.twig', [
            'projects'=>$projects,
            'projectsName' => $projectNames,
            'projectsColors'=>$projectColors,
            'projectsCosts'=>$projectCosts,
            'projectsDocs'=>$projectDocs,
            'projectsSteps'=>$projectSteps
        ]);
    }
    #[Route('/chart/{project}', name: 'chart_project', methods: ['GET'])]
    public function project(ProjectRepository $projectRepository, Request $request): Response
    {

        $projectSlug = $request->get('project');
        $project = $projectRepository->findOneBy(['slug'=>$projectSlug]);
        $costs = $project->getCosts();
        $costsMonth = [
            'January'=>0,
            'February'=>0,
            'March'=>0,
            'April'=>0,
            'May'=>0,
            'June'=>0,
            'July'=>0,
            'August'=>0,
            'September'=>0,
            'October'=>0,
            'November'=>0,
            'December'=>0,
            ];

        foreach($costs as $cost){
            $month = date('F',$cost->getCreatedAt()->getTimestamp());
            $costsMonth[$month] += $cost->getEur();
        }

        return $this->render('chart/project.html.twig',[
            'months'=>array_keys($costsMonth),
            'costs' => array_values($costsMonth)
        ]);

    }
}
