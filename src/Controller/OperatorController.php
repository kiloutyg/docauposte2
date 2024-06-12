<?php

namespace App\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Form\OperatorType;

use App\Entity\Operator;
use App\Entity\TrainingRecord;
use App\Entity\Trainer;


class OperatorController extends FrontController
{

    #[Route('/operator', name: 'app_operator')]
    public function operatorBasePage(Request $request): Response
    {
        $this->logger->info('search query with full request', $request->request->all());
        // if ($app . user) Was doing somthing here and i don't remember what
        $countArray = $this->operatorService->operatorCheckForAutoDelete();
        if ($countArray != null) {
            if ($countArray != null) {
                $this->addFlash('info', ($countArray['inActiveOperators'] === 1 ? $countArray['inActiveOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['inActiveOperators'] . ' opérateurs inactifs sont à supprimer. ') .
                    ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
            }
        }
        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator);
        $newOperatorForm->handleRequest($request);

        if ($newOperatorForm->isSubmitted() && $newOperatorForm->isValid()) {
            try {
                $this->processNewOperator($newOperator, $newOperatorForm, $request);
                $this->addFlash('success', 'L\'opérateur a bien été ajouté');
                return $this->redirectToRoute('app_operator');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être ajouté' . $e->getMessage());
                return $this->redirectToRoute('app_operator');
            }
        }


        $operators = [];
        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $this->logger->info(' the used method is a post');
            if ($request->getContentTypeFormat() == 'json') {
                $this->logger->info('is the content type a json');
                $data = json_decode($request->getContent(), true);
                $this->logger->info('data', $data);
                $name       = $data['search_name'];
                $code       = $data['search_code'];
                $team       = $data['search_team'];
                $uap        = $data['search_uap'];
                $trainer    = $data['search_trainer'];
            } else {
                $this->logger->info('is the content type a form');
                $name       = $request->request->get('search_name');
                $code       = $request->request->get('search_code');
                $team       = $request->request->get('search_team');
                $uap        = $request->request->get('search_uap');
                $trainer    = $request->request->get('search_trainer');
            }
            $operators = $this->operatorRepository->findBySearchQuery($name, $code, $team, $uap, $trainer);
        }


        $operatorForms = [];
        // Create and handle forms
        foreach ($operators as $operator) {
            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ])->createView();
        }

        $this->logger->info('in operatorBasePage is operatorForms empty: ' . count($operatorForms));

        if (count($operatorForms) === 0) {
            $inActiveOperators = $this->operatorRepository->findInActiveOperators();
            $this->logger->info('in operatorBasePage is inActiveOperators : ' . count($inActiveOperators));

            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }

        return $this->render('services/operators/operators_admin.html.twig', [
            'newOperatorForm' => $newOperatorForm->createView(),
            'operatorForms' => $operatorForms,
        ]);
    }

    private function processNewOperator(Operator $newOperator, $form, Request $request)
    {

        $trainerBool = $form->get('isTrainer')->getData();
        if ($trainerBool == true) {
            $trainer = new Trainer();
            $trainer->setOperator($newOperator);
            $this->em->persist($trainer);
            $newOperator->setTrainer($trainer);
        } else if ($trainerBool != true) {
            $trainer = $newOperator->getTrainer();
            $newOperator->setTrainer(null);
            if ($trainer != null) {
                $this->em->remove($trainer);
            }
        };
        $operator = $form->getData();
        $this->em->persist($operator);
        $this->em->flush();
    }





    // Individual operator modification controller, used in dev purpose
    #[Route('/operator/edit/{id}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, Operator $operator): Response
    {
        $this->logger->info('Full request editOperatorAction', $request->request->all());

        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trainerBool = $form->get('isTrainer')->getData();
            if ($trainerBool == true) {
                if ($operator->getTrainer() == null) {
                    $trainer = new Trainer();
                    $trainer->setOperator($operator);
                    $this->em->persist($trainer);
                    $operator->setTrainer($trainer);
                }
            } else {
                $trainer = $operator->getTrainer();
                $operator->setTrainer(null);
                if ($trainer != null) {
                    $this->em->remove($trainer);
                }
            }
            if ($operator->getTobedeleted() != null) {
                $operator->setTobedeleted(null);
                $operator->setLasttraining(new \DateTime());
            }
            try {
                $this->em->persist($operator);
                $this->em->flush();
                $this->logger->info('operator, operateur bien modifié', [$operator]);
                $this->addFlash('success', 'L\'opérateur a bien été modifié');
                return $this->redirectToRoute('app_operator');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être modifié. Erreur: ' . $e->getMessage());
                $this->logger->error('Error while editing operator in try catch', [$e->getMessage()]);
                return $this->redirectToRoute('app_operator');
            }
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->logger->error('Error in submitting form while editing operator');
            $this->addFlash('danger', 'Erreur lors de la soumission du formulaire');
            return $this->redirectToRoute('app_operator');
        }
        $this->logger->info('editoperatoraction reach return to template"');
        return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
            'form' => $form->createView(),
            'operator' => $operator
        ]);
    }



    // Route to delete operator from the administrator view
    #[Route('/operator/delete/{id}', name: 'app_operator_delete')]
    public function deleteOperatorAction(int $id): Response
    {

        $result = $this->entitydeletionService->deleteEntity('operator', $id);

        if (!$result) {
            $this->addFlash('danger', 'L\'opérateur n\'a pas pu être supprimé');
            return $this->redirectToRoute('app_operator');
        } else {
            $this->addFlash('success', 'L\'opérateur a bien été supprimé');
            return $this->redirectToRoute('app_operator');
        }
    }




    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/frontByVal/{validationId}', name: 'app_training_front_by_validation')]
    public function documentAndOperatorByValidation(Request $request, int $validationId): Response
    {
        $validation = $this->validationRepository->find($validationId);
        $upload = $validation->getUpload();
        $countArray = $this->operatorService->operatorCheckForAutoDelete();
        // if ($countArray != null) {
        //     if ($countArray != null) {
        //         $this->addFlash('info', ($countArray['inActiveOperators'] === 1 ? $countArray['inActiveOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['inActiveOperators'] . ' opérateurs inactifs sont à supprimer. ') .
        //             ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
        //     }
        // }

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,
            ]);
        }
    }

    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/frontByUpl/{uploadId}', name: 'app_training_front_by_upload')]
    public function documentAndOperatorByUpload(Request $request, int $uploadId): Response
    {
        $upload = $this->uploadRepository->getUpload($uploadId);
        $countArray = $this->operatorService->operatorCheckForAutoDelete();
        // if ($countArray != null) {
        //     if ($countArray != null) {
        //         $this->addFlash('info', ($countArray['inActiveOperators'] === 1 ? $countArray['inActiveOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['inActiveOperators'] . ' opérateurs inactifs sont à supprimer. ') .
        //             ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
        //     }
        // }

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

        $surname = $request->request->get('newOperatorSurname');
        $firstname = $request->request->get('newOperatorFirstname');
        $concatenedOperatorNameNotLower = $firstname . '.' . $surname;
        $concatenedOperatorNameLower = strtolower($concatenedOperatorNameNotLower);

        $operatorName = $request->request->get('newOperatorName');

        if ($operatorName !== $concatenedOperatorNameLower) {
            $this->addFlash('danger', 'Il y a eu un probleme, contactez votre administrateur');
            return $this->redirectToRoute('app_training_list', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }

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

        $selectedOperators = $this->operatorRepository->findBy(['team' => $teamId, 'uap' => $uapId], ['team' => 'ASC', 'uap' => 'ASC']);

        usort($selectedOperators, function ($a, $b) {
            list($firstNameA, $surnameA) = explode('.', $a->getName());
            list($firstNameB, $surnameB) = explode('.', $b->getName());

            return $surnameA === $surnameB ? strcmp($firstNameA, $firstNameB) : strcmp($surnameA, $surnameB);
        });

        $this->logger->info('selectedOperators', [$selectedOperators]);


        $trainingRecords = []; // Array of training records
        $unorderedTrainingRecords = []; // Array of unordered training records
        $untrainedOperators = []; // Array of untrained operators
        $operatorsByTrainer = []; // Array of operators grouped by trainer
        $inTrainingOperatorsByTrainer = []; // Array of operators in training grouped by trainer

        foreach ($selectedOperators as $operator) {
            $records = $this->trainingRecordRepository->findBy(['operator' => $operator, 'Upload' => $uploadId]);
            $unorderedTrainingRecords = array_merge($trainingRecords, $records);

            $record = $records[0] ?? null;
            if ($record) {
                $this->logger->info('unorderedTrainingRecords', [$unorderedTrainingRecords]);
                $trainerName = $record->getTrainer() ? $record->getTrainer()->getOperator()->getName() : 'inconnu.nom';
                if ($record->isTrained()) {
                    $operatorsByTrainer[$trainerName][] = $operator;
                } else {
                    $inTrainingOperatorsByTrainer[$trainerName][] = $operator;
                }
            } else {
                $untrainedOperators[] = $operator;
            }
        }


        if (!empty($unorderedTrainingRecords)) {
            $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByTrainingRecordsArray($unorderedTrainingRecords);
        }


        $this->logger->info('trainingRecords', [$trainingRecords]);

        // Render the partial view
        return $this->render('services/operators/training_component/_listOperatorContainer.html.twig', [
            'team' => $this->teamRepository->find($teamId),
            'uap' => $this->uapRepository->find($uapId),
            'upload' => $upload,
            'selectedOperators' => $selectedOperators,
            'trainingRecords'   => $trainingRecords,
            'untrainedOperators' => $untrainedOperators,
            'operatorsByTrainer' => $operatorsByTrainer,
            'inTrainingOperatorsByTrainer' => $inTrainingOperatorsByTrainer,
        ]);
    }

    #[Route('/operator/trainingRecord/form/{uploadId}/{teamId}/{uapId}', name: 'app_training_record_form')]
    public function trainingRecordForm(int $uploadId, Request $request, ?int $teamId = null, ?int $uapId = null): Response
    {
        $this->logger->info('Full request', $request->request->all());
        $operators = [];
        $operators = $request->request->all('operators');
        $upload = $this->uploadRepository->find($uploadId);
        $trainerId = $request->request->get('trainerId');

        $trainerEntityWithUpload = $this->trainerRepository->findOneBy(['operator' => $trainerId, 'upload' => $upload]);
        if ($trainerEntityWithUpload == null) {
            $this->logger->info('operator ID', [$trainerId]);
            $trainerOperator = $this->operatorRepository->find($trainerId);
            $trainerEntity = $this->trainerRepository->findOneBy(['operator' => $trainerOperator]);
            $this->logger->info('trainerEntity', [$trainerEntity]);
        } else {
            $this->logger->info('trainerEntityWithUpload', [$trainerEntityWithUpload]);
            $trainerOperator = $this->operatorRepository->find($trainerId);
        };

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
                        $existingTrainingRecord->setTrainer($trainerEntity);
                        $existingTrainingRecord->setDate(new \DateTime());
                        $this->em->persist($existingTrainingRecord);
                        $operatorEntity->setLasttraining(new \DateTime());
                        $operatorEntity->setTobedeleted(null);
                        $this->em->persist($operatorEntity);
                    }
                } else {
                    // If the collection was empty, create a new TrainingRecord
                    $trainingRecord = new TrainingRecord();
                    $trainingRecord->setOperator($operatorEntity);
                    $trainingRecord->setUpload($upload);
                    $trainingRecord->setDate(new \DateTime());
                    $trainingRecord->setTrained($trained);
                    $trainingRecord->setTrainer($trainerEntity);
                    $this->em->persist($trainingRecord);
                    $operatorEntity->setLasttraining(new \DateTime());
                    $operatorEntity->setTobedeleted(null);
                    $this->em->persist($operatorEntity);
                }

                // Flush changes for each operator
                $this->em->flush();
            }
        }

        $trainerOperator->setLasttraining(new \DateTime());
        $this->em->persist($trainerOperator);
        $this->em->flush();

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

        $controllerOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode, 'team' => $teamId, 'uap' => $uapId]);
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

        if (key_exists('uploadId', $parsedRequest)) {
            $uploadId = $parsedRequest['uploadId'];
            $this->logger->info('uploadId', [$uploadId]);
            $upload = $this->uploadRepository->find($uploadId);
        } else {
            $upload = null;
        };

        if ($enteredCode != null) {
            $existingOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode, 'name' => $enteredName, 'IsTrainer' => true]);
            if ($existingOperator !== null) {
                $uploadTrainer = $this->trainerRepository->findOneBy(['operator' => $existingOperator, 'upload' => $upload]);
                if ($uploadTrainer !== null) {
                    return new JsonResponse([
                        'found'         => true,
                        'name'          => $existingOperator->getName(),
                        'code'          => $existingOperator->getCode(),
                        'trainerId'     => $existingOperator->getId(),
                        'uploadTrainer' => true,
                    ]);
                }
                return new JsonResponse([
                    'found'         => true,
                    'name'          => $existingOperator->getName(),
                    'code'          => $existingOperator->getCode(),
                    'trainerId'     => $existingOperator->getId(),
                    'uploadTrainer' => false,

                ]);
            } else {
                return new JsonResponse([
                    'found' => false,
                ]);
            }
        } else {
            $existingOperator = $this->operatorRepository->findOneBy(['name' => $enteredName, 'IsTrainer' => true]);
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

    #[Route('operator/suggest-names', name: 'app_suggest_names')]
    public function suggestNames(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $parsedRequest = json_decode($request->getContent(), true);
        $this->logger->info('app_suggest_names parsedRequest', $parsedRequest);

        $name = $parsedRequest['name'];

        /////////////// serialized data ////////////////////////
        $rawSuggestions = $this->operatorRepository->findByNameLikeForSuggestions($name);
        $this->logger->info('app_suggest_names Raw suggestions', $rawSuggestions);

        $teams = $this->teamRepository->findAll();
        $teamIndex = [];
        foreach ($teams as $team) {
            $teamIndex[$team->getId()] = $team->getName();
        }

        $uaps = $this->uapRepository->findAll();
        $uapIndex = [];
        foreach ($uaps as $uap) {
            $uapIndex[$uap->getId()] = $uap->getName();
        }


        foreach ($rawSuggestions as $key => &$suggestion) {
            // Check and assign team name if available
            if (isset($suggestion['team_id']) && isset($teamIndex[$suggestion['team_id']])) {
                $suggestion['team_name'] = $teamIndex[$suggestion['team_id']];
            } else {
                $suggestion['team_name'] = 'nope'; // Or handle it as appropriate
            }

            // Check and assign UAP name if available
            if (isset($suggestion['uap_id']) && isset($uapIndex[$suggestion['uap_id']])) {
                $suggestion['uap_name'] = $uapIndex[$suggestion['uap_id']];
            } else {
                $suggestion['uap_name'] = 'nope'; // Or handle it as appropriate
            }
        }

        $this->logger->info('app_suggest_names rawSuggestions', $rawSuggestions);
        // Serialize the entire array of entities at once using groups
        $serializedSuggestions = json_encode($rawSuggestions);
        // $serializedSuggestions = $serializer->serialize($rawSuggestions, 'json', [
        //     'groups' => 'operator_details'
        // ]);

        // $this->logger->info('app_suggest_names serialized suggestions', json_decode($serializedSuggestions));
        // $this->logger->info('app_suggest_names serialized suggestions', json_decode($serializedSuggestions));

        // Since $serializedSuggestions is a JSON string, return it directly with JsonResponse
        return new JsonResponse($serializedSuggestions, 200, [], true);
    }
}
