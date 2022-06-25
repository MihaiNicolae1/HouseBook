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
        $documents = $project->getDocuments();
        $costsYear = $documentYear = array();
        $stepCost = ['No Step' => 0];
        $costsMonth = $documentMonth = [
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
            $year = date('Y',$cost->getCreatedAt()->getTimestamp());
            empty($cost->getSteps()) ? $stepCost['No Step'] = 0 : $stepCost[$cost->getSteps()->getName()] = 0;
            $costsYear[$year] = 0;
        }
        foreach($documents as $document){
            $year = date('Y',$document->getCreatedAt()->getTimestamp());
            $documentYear[$year] = 0;
        }

        foreach($costs as $cost){
            $month = date('F',$cost->getCreatedAt()->getTimestamp());
            $year = date('Y',$cost->getCreatedAt()->getTimestamp());
            if($year === date('Y'))
                $costsMonth[$month] += $cost->getEur();
            $costsYear[$year] += $cost->getEur();
        }
        foreach($documents as $document){
            $month = date('F',$document->getCreatedAt()->getTimestamp());
            $year = date('Y',$document->getCreatedAt()->getTimestamp());
            if($year === date('Y'))
                $documentMonth[$month] += 1;
            $documentYear[$year] += 1;
        }

        foreach($costs as $cost){
            empty($cost->getSteps()) ? $stepCost['No Step'] += $cost->getEur() :  $stepCost[$cost->getSteps()->getName()] += $cost->getEur();
        }
        return $this->render('chart/project.html.twig',[
            'months'=>array_keys($costsMonth),
            'monthCosts' => array_values($costsMonth),
            'years' =>array_keys($costsYear),
            'yearCosts' => array_values($costsYear),
            'docmonths'=>array_keys($documentMonth),
            'docmonthCosts' => array_values($documentMonth),
            'docyears' =>array_keys($documentYear),
            'docyearCosts' => array_values($documentYear),
            'stepNames' =>array_keys($stepCost),
            'stepCosts' => array_values($stepCost),

        ]);

    }
}
