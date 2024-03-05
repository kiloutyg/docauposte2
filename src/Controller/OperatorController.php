<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Form\OperatorType;

use App\Entity\Operator;
use App\Entity\TrainingRecord;


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
    #[Route('operator/traininglist/{uploadId}', name: 'app_training_list')]
    public function trainingList(int $uploadId): Response
    {
        // Handle the GET request
        $upload = $this->uploadRepository->find($uploadId);
        return $this->render('services/operators/operatorTraining.html.twig', [
            'trainingRecords' => $upload->getTrainingRecords(),
            'operators' => $this->operatorRepository->findAll(),
            'upload' => $upload,
        ]);
    }


    // Route to handle the newOperator form submission
    #[Route('/operator/traininglist/new/{uploadId}/{teamId}/{uapId}', name: 'app_training_new_operator')]
    public function trainingListNewOperator(ValidatorInterface $validator, Request $request, int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {

        $this->logger->info('Full request', $request->request->all());
        $originUrl = $request->headers->get('referer');
        $team = $this->teamRepository->find($teamId);
        $this->logger->info('team', [$team->getName()]);
        $uap = $this->uapRepository->find($uapId);
        $this->logger->info('uap', [$uap->getName()]);

        $operatorName = $request->request->get('newOperator');

        $existingOperator = $this->operatorRepository->findOneBy(['name' => $operatorName]);
        if ($existingOperator != null) {
            $this->logger->info('existingOperator', [$existingOperator->getName()]);
            if ($existingOperator->getTeam() == $team && $existingOperator->getUap() == $uap) {
                $this->addFlash('danger', 'Cet opérateur existe déjà');
                return $this->redirectToRoute('app_training_list', [
                    'uploadId' => $uploadId,
                    'teamId' => $teamId,
                    'uapId' => $uapId,
                ]);
            } else {
                $existingOperator->setTeam($team);
                $existingOperator->setUap($uap);
                $this->em->persist($existingOperator);
                $this->em->flush();
                $this->addFlash('success', 'L\'opérateur a bien été ajouté et son equipe et son UAP ont été modifiées');
                return $this->redirectToRoute('app_render_training_records', [
                    'uploadId' => $uploadId,
                    'teamId' => $teamId,
                    'uapId' => $uapId,
                ]);
            }
        }

        $operator = new Operator();
        $operator->setName($operatorName);
        $operator->setTeam($team);
        $operator->setUap($uap);

        $errors = $validator->validate($operator);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                // You can use ->getPropertyPath() if you need to show the field name
                // $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                $errorMessages[] = $violation->getMessage();
            }

            // Now you have an array of user-friendly messages you can display
            // For example, you can separate them with new lines when displaying in text format:
            $errorsString = implode("\n", $errorMessages);

            // If you need to return JSON response:
            // return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);

            $this->addFlash('danger', $errorsString);
            return $this->redirectToRoute('app_render_training_records', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }

        $this->em->persist($operator);
        $this->em->flush();
        $this->addFlash('success', 'L\'opérateur a bien été ajouté');
        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }


    #[Route('/operator/traininglist/listform/{uploadId}', name: 'app_training_list_select_record_form')]
    public function trainingListFormHandling(Request $request, int $uploadId): Response
    {
        // Log the full request for debugging
        $this->logger->info('Full request', $request->request->all());

        // Process the POST request

        $teamId = $request->request->get('team-trainingRecord-select');
        $uapId = $request->request->get('uap-trainingRecord-select');
        if ($teamId == null || $uapId == null) {
            $this->addFlash('danger', 'Veuillez sélectionner une équipe et une UAP');
            return $this->redirectToRoute('app_training_list', ['uploadId' => $uploadId]);
        }

        // Redirect to the route that renders the partial
        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }


    #[Route('/operator/render-training-records/{uploadId}/{teamId}/{uapId}', name: 'app_render_training_records')]
    public function renderTrainingRecords(int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {
        $upload = $this->uploadRepository->find($uploadId);

        $selectedOperators = $this->operatorRepository->findBy(['Team' => $teamId, 'uap' => $uapId]);
        $trainingRecords = [];

        foreach ($selectedOperators as $operator) {
            $records = $this->trainingRecordRepository->findBy(['operator' => $operator, 'Upload' => $uploadId]);
            $trainingRecords = array_merge($trainingRecords, $records);
        }
        // $this->addFlash('success', 'Les opérateurs ont bien été ajoutés à la liste de formation');
        // Render the partial view
        return $this->render('services/operators/component/_listOperator.html.twig', [
            'team' => $this->teamRepository->find($teamId),
            'uap' => $this->uapRepository->find($uapId),
            'upload' => $upload,
            'selectedOperators' => $selectedOperators,
            'trainingRecords'   => $trainingRecords,
        ]);
    }

    #[Route('/operator/trainingRecord/form/{uploadId}/{teamId}/{uapId}', name: 'app_training_record_form')]
    public function trainingRecordForm(int $uploadId, Request $request, ?int $teamId = null, ?int $uapId = null): Response
    {
        $this->logger->info('Full request', $request->request->all());
        $operators = [];
        $operators = $request->request->all('operators');
        $upload = $this->uploadRepository->find($uploadId);

        foreach ($operators as $operator) {
            $this->logger->info('does the key exist', [array_key_exists("trained", $operator)]);

            if (array_key_exists("trained", $operator)) {

                $this->logger->info('operator', [$operator]);
                $operatorEntity = $this->operatorRepository->find($operator['id']);
                $trained = ($operator['trained'] === '') ? null : (($operator['trained'] === 'true') ? true : false);

                $this->logger->info('operator name and is he trained',     [
                    'name'    => $operatorEntity->getName(),
                    'trained' => $trained
                ]);
                if ($trained === null) {
                    break;
                }
                $trainingRecord = new TrainingRecord();
                $trainingRecord->setOperator($operatorEntity);
                $trainingRecord->setUpload($upload);
                $trainingRecord->setDate(new \DateTime());
                $trainingRecord->setTrained($trained);
                $this->em->persist($trainingRecord);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }
}
