<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Form\OperatorType;

use App\Entity\Operator;
use App\Entity\TrainingRecord;
use App\Entity\Trainer;


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
        if (isset($operators)) {
            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }
        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator, [
            'operator_id' => $operator->getId(),
        ]);
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
        $this->logger->info('Full request', $request->request->all());
        $originUrl = $request->headers->get('referer');

        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trainerBool = $form->get('isTrainer')->getData();
            if ($trainerBool == true) {
                $trainer = new Trainer();
                $trainer->setOperator($operator);
                $this->em->persist($trainer);
                $operator->setTrainer($trainer);
            } else {
            };

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

        $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByUpload($upload);

        return $this->render('services/operators/operatorTraining.html.twig', [
            'trainingRecords'   => $trainingRecords,
            'upload'            => $upload,
        ]);
    }


    // Route to handle the newOperator form submission
    #[Route('/operator/traininglist/newOperator/{uploadId}/{teamId}/{uapId}', name: 'app_training_new_operator')]
    public function trainingListNewOperator(ValidatorInterface $validator, Request $request, int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {

        $this->logger->info('Full request', $request->request->all());
        $team = $this->teamRepository->find($teamId);
        $this->logger->info('team', [$team->getName()]);
        $uap = $this->uapRepository->find($uapId);
        $this->logger->info('uap', [$uap->getName()]);
        $operatorCode = $request->request->get('newOperatorCode');

        $operatorName = $request->request->get('newOperatorName');

        $existingOperator = $this->operatorRepository->findOneBy(['name' => $operatorName]);
        if ($existingOperator == null) {
            $existingOperator = $this->operatorRepository->findOneBy(['code' => $operatorCode]);
        }

        if ($existingOperator != null) {
            $this->logger->info('existingOperator', [$existingOperator->getName()]);
            if ($existingOperator->getTeam() == $team && $existingOperator->getUap() == $uap) {
                $this->addFlash('danger', 'Cet opérateur existe déjà dans cette equipe et uap');
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
        $operator->setCode($operatorCode);

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

            $this->logger->info('danger', [$errorsString]);
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

        $selectedOperators = $this->operatorRepository->findBy(['Team' => $teamId, 'uap' => $uapId], ['Team' => 'ASC', 'uap' => 'ASC', 'name' => 'ASC']);
        $this->logger->info('selectedOperators', [$selectedOperators]);


        $trainingRecords = [];
        foreach ($selectedOperators as $operator) {
            $records = $this->trainingRecordRepository->findBy(['operator' => $operator, 'Upload' => $uploadId]);
            $unorderedTrainingRecords = array_merge($trainingRecords, $records);
        }
        if (!empty($unorderedTrainingRecords)) {
            $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByTrainingRecordsArray($unorderedTrainingRecords);
        }
        $this->logger->info('trainingRecords', [$trainingRecords]);

        // Render the partial view
        return $this->render('services/operators/component/_listOperatorContainer.html.twig', [
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
        $trainer = $request->request->get('trainer');

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

                // Get all training records as a collection
                $operatorTrainingRecords = $operatorEntity->getTrainingRecords();
                // Filter the collection to find the record with the matching $upload
                $filteredRecords = $operatorTrainingRecords->filter(function ($trainingRecord) use ($upload) {
                    return $trainingRecord->getUpload() === $upload;
                });

                // Check if a TrainingRecord exists in the filtered collection
                if (!$filteredRecords->isEmpty()) {
                    $existingTrainingRecord = $filteredRecords->first();

                    // Make sure $existingTrainingRecord is indeed a TrainingRecord instance
                    if ($existingTrainingRecord instanceof TrainingRecord) {
                        $existingTrainingRecord->setTrained($trained);
                        $existingTrainingRecord->setDate(new \DateTime());
                        $this->em->persist($existingTrainingRecord);
                    }
                } else {
                    // If the collection was empty, create a new TrainingRecord
                    $trainingRecord = new TrainingRecord();
                    $trainingRecord->setOperator($operatorEntity);
                    $trainingRecord->setUpload($upload);
                    $trainingRecord->setDate(new \DateTime());
                    $trainingRecord->setTrained($trained);
                    $this->em->persist($trainingRecord);
                }

                // Flush changes for each operator
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }


    #[Route('/operator/check-duplicate-by-name', name: 'app_operator_check_duplicate_by_name', methods: ['POST'])]
    public function checkDuplicateOperatorByName(Request $request): JsonResponse
    {
        $this->logger->info('Full requestbyname', [$request->request->all()]);

        $parsedRequest = json_decode($request->getContent(), true);
        $this->logger->info('parsedRequest', [$parsedRequest]);

        $operatorName = $parsedRequest['value'];
        $this->logger->info('operatorName', [$operatorName]);

        $existingOperator = $this->operatorRepository->findOneBy(['name' => $operatorName]);
        $this->logger->info('existingOperator', [$existingOperator]);

        if ($existingOperator !== null) {
            // Found duplicate
            return new JsonResponse([
                'found' => true,
                'field' => 'name',
                'value' => $operatorName,
                'message' => 'Un opérateur avec ce nom existe déjà',
                'operator' => [
                    'id' => $existingOperator->getId(),
                    // Include additional details as necessary
                ]
            ]);
        }


        // No duplicate found
        return new JsonResponse([
            'found' => false, 'field' => 'name', 'value' => $operatorName, 'message' => 'Aucun opérateur avec ce nom n\'existe'
        ]);
    }


    #[Route('/operator/check-duplicate-by-code', name: 'app_operator_check_duplicate_by_code', methods: ['POST'])]
    public function checkDuplicateOperatorByCode(Request $request): JsonResponse
    {
        $this->logger->info('Full request bycode', $request->request->all());

        $parsedRequest = json_decode($request->getContent(), true);
        $this->logger->info('parsedRequest', [$parsedRequest]);

        $operatorCode = $parsedRequest['value'];
        $this->logger->info('operatorCode', [$operatorCode]);

        $existingOperator = $this->operatorRepository->findOneBy(['code' => $operatorCode]);
        $this->logger->info('existingOperator', [$existingOperator]);

        if ($existingOperator !== null) {
            // Found duplicate
            return new JsonResponse([
                'found' => true,
                'field' => 'code',
                'value' => $operatorCode,
                'message' => 'Un opérateur avec ce codeOpé existe déjà',
                'operator' => [
                    'id' => $existingOperator->getId(),
                    // Include additional details as necessary
                ]
            ]);
        }

        // No duplicate found
        return new JsonResponse([
            'found' => false, 'field' => 'code', 'value' => $operatorCode, 'message' => "Aucun opérateur avec ce codeOpé n'existe"
        ]);
    }


    // Route to check the operator to validate the training form and make the trained button appear

    #[Route('operator/check-entered-code-against-operator-code/{teamId}/{uapId}', name: 'app_check_entered_code_against_operator_code')]
    public function checkEnteredCodeAgainstOperatorCode(Request $request, int $teamId, int $uapId): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);

        $this->logger->info('Full request', $parsedRequest);

        $enteredCode = $parsedRequest['code'];
        $this->logger->info('enteredCode', [$enteredCode]);

        $operatorId = (int)$parsedRequest['operatorId'];
        $this->logger->info('operatorId', [$operatorId]);

        $controllerOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode, 'Team' => $teamId, 'uap' => $uapId]);
        $this->logger->info('controllerOperator', [$controllerOperator]);

        if ($controllerOperator != null) {
            $controllerOperatorId = $controllerOperator->getId();
            $this->logger->info('controllerOperatorId', [$controllerOperatorId]);

            $controllerOperatorId === $operatorId ? $operator = $controllerOperator : $operator = null;
            $this->logger->info('operator', [$operator]);

            if ($operator !== null) {
                // Found operator
                return new JsonResponse([
                    'found' => true,
                    'operator' => [
                        'id' => $operator->getId(),
                        'name' => $operator->getName(),
                        'code' => $operator->getCode(),
                        'team' => $operator->getTeam()->getName(),
                        'uap' => $operator->getUap()->getName(),
                    ]
                ]);
            }
        }
        // No operator found
        return new JsonResponse([
            'found' => false,
            'message' => 'Aucun opérateur avec ce code n\'existe dans cette équipe et cette UAP'
        ]);
    }


    // Route to check if a code exist in the database and then return a boolean

    #[Route('operator/check-if-code-exist', name: 'app_check_if_code_exist')]
    public function checkIfCodeExist(Request $request): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);

        $this->logger->info('Full request', $parsedRequest);

        $enteredCode = $parsedRequest['code'];
        $this->logger->info('enteredCode', [$enteredCode]);

        $existingOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode]);
        if ($existingOperator !== null) {

            return new JsonResponse([
                'found' => true,
            ]);
        } else {
            return new JsonResponse([
                'found' => false,
            ]);
        }
    }

    // Route to check if a trainer exist by name and code
    #[Route('operator/check-if-trainer-exist', name: 'app_check_if_trainer_exist')]
    public function checkIfTrainerExist(Request $request): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);

        $this->logger->info('Full request', $parsedRequest);

        if (key_exists('code', $parsedRequest)) {
            $enteredCode = $parsedRequest['code'];
            $this->logger->info('enteredCode', [$enteredCode]);
        } else {
            $enteredCode = null;
        };

        if (key_exists('name', $parsedRequest)) {
            $enteredName = $parsedRequest['name'];
            $this->logger->info('enteredName', [$enteredName]);
        } else {
            $enteredName = null;
        };

        if ($enteredCode != null) {
            $existingOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode, 'name' => $enteredName]);
            if ($existingOperator !== null) {

                return new JsonResponse([
                    'found'     => true,
                    'name'      => $existingOperator->getName(),
                    'code'      => $existingOperator->getCode(),
                    'trainerId' => $existingOperator->getId(),
                ]);
            } else {
                return new JsonResponse([
                    'found' => false,
                ]);
            }
        } else {
            $existingOperator = $this->operatorRepository->findOneBy(['name' => $enteredName]);
            if ($existingOperator !== null) {

                return new JsonResponse([
                    'found' => true,
                    'name'  => $existingOperator->getName(),
                    'code'  => $existingOperator->getCode(),
                ]);
            } else {
                return new JsonResponse([
                    'found' => false,
                ]);
            }
        }
    }
}
