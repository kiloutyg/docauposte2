<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Form\OperatorType;

use App\Entity\Operator;

class OperatorController extends FrontController
{

    // Route to have the basic page being display for dev purpose
    #[Route('/operator', name: 'app_operator')]
    public function operatorBasePage(Request $request): Response
    {
        $originUrl = $request->headers->get('referer');

        if ($this->operatorRepository->findAll() != null) {
            $operators = $this->operatorRepository->findAll();
        }
        $operatorForms = [];

        foreach ($operators as $operator) {
            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator)->createView();
        }

        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator);
        if ($request->getMethod() === 'POST') {
            $newOperatorForm->handleRequest($request);
            if ($newOperatorForm->isSubmitted() && $newOperatorForm->isValid()) {
                $operator = $newOperatorForm->getData();
                $this->em->persist($operator);
                $this->em->flush();
                $this->addFlash('success', 'L\'opérateur a bien été ajouté');
                return $this->redirect($originUrl);
            }
        } else if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/operators.html.twig', [
                'newOperatorForm' => $newOperatorForm->createView(),
                'operatorForms' => $operatorForms,
            ]);
        }
    }


    // individual operator modification controller, used in dev purpose
    #[Route('/operator/edit/{id}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, Operator $operator): Response
    {
        $originUrl = $request->headers->get('referer');

        $form = $this->createForm(OperatorType::class, $operator);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operator = $form->getData();
            $this->em->persist($operator);
            $this->em->flush();
            $this->addFlash('success', 'L\'opérateur a bien été ajouté');
            return $this->redirect($originUrl);
        } else {
            return $this->render('app_operator');
            $this->addFlash(
                'danger',
                'cdlm'
            );
        }

        // Redirect back to the main operator page or render a specific template
    }

    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/visual/{validationId}', name: 'app_test_document')]
    public function documentAndOperator(Request $request, int $validationId): Response
    {
        $originUrl = $request->headers->get('referer');
        $validation = $this->validationRepository->find($validationId);
        $upload = $validation->getUpload();

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,

            ]);
        }
    }

    // page with the training record and the operator list and the form to add a new operator, 
    // page that will be integrated as an iframe probably in the test document page
    #[Route('operator/trainginglist/{uploadId}', name: 'app_training_list')]
    public function trainingList(Request $request, int $uploadId): Response
    {
        $upload = $this->uploadRepository->find($uploadId);
        $trainingRecords = $upload->getTrainingRecords();

        return $this->render('services/operators/operatorTraining.html.twig', [
            'trainingRecords' => $trainingRecords,

        ]);
    }
}
