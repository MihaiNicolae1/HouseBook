<?php

namespace App\Controller;

use App\Entity\Cost;
use App\Form\CostType;
use App\Repository\CostRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\CursBNR;


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
                    //Getting the EUR value and setting it 
                    $eurValue = $form->get("value")->getData();
                    $cost->setEur($eurValue);

                    //Getting the RON-EUR exchange rate
                    $eurExchangeRate = $curs->getExchangeRate("EUR");
                    //Calculating the equivalent in RON
                    $ronValue = $eurValue * $eurExchangeRate;
                    $ronValue = round($ronValue,2);
                    //Setting the RON value
                    $cost->setRon($ronValue);

                    //Getting the RON-USD exchange rate
                    $usdExchangeRate = $curs->getExchangeRate("USD");
                    //Calculating the equivalent in USD
                    $usdValue = $ronValue / $usdExchangeRate;
                    $usdValue = round($usdValue,2);
                    //Setting the USD value
                    $cost->setUsd($usdValue);
                }elseif($currencyChoice == "USD"){
                    //Getting the EUR value and setting it 
                    $usdValue = $form->get("value")->getData();
                    $cost->setUsd($usdValue);

                    //Getting the RON-USD exchange rate
                    $usdExchangeRate = $curs->getExchangeRate("USD");
                    //Calculating the equivalent in RON
                    $ronValue = $usdValue * $usdExchangeRate;
                    $ronValue = round($ronValue,2);
                    //Setting the RON value
                    $cost->setRon($ronValue);

                    //Getting the RON-EUR exchange rate
                    $eurExchangeRate = $curs->getExchangeRate("EUR");
                    //Calculating the equivalent in EUR
                    $eurValue = $ronValue / $eurExchangeRate;
                    $eurValue = round($eurValue,2);
                    //Setting the EUR value
                    $cost->setEur($eurValue);
                }elseif($currencyChoice == "RON"){
                    //Getting the RON value and setting it
                    $ronValue = $form->get("value")->getData();
                    $cost->setRon($ronValue);

                    //Getting the RON-USD exchange rate
                    $usdExchangeRate = $curs->getExchangeRate("USD");
                    //Calculating the equivalent in RON
                    $usdValue = $ronValue / $usdExchangeRate;
                    $usdValue = round($usdValue,2);
                    //Setting the USD value
                    $cost->setUsd($usdValue);


                    //Getting the RON-EUR exchange rate
                    $eurExchangeRate = $curs->getExchangeRate("EUR");
                    //Calculating the equivalent in EUR
                    $eurValue = $ronValue / $eurExchangeRate;
                    $eurValue = round($eurValue,2);
                    //Setting the EUR value
                    $cost->setEur($eurValue);
                    //Adding the cost to the project

                }
            // Add the value to the costs of the project
            $project->addCostsToProject($eurValue, $usdValue, $ronValue);
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
