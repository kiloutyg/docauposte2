<?php

namespace App\Controller;

use \Psr\Log\LoggerInterface;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Contracts\Cache\CacheInterface;

use App\Form\OperatorType;
use App\Form\TeamType;
use App\Form\UapType;

use App\Entity\Operator;
use App\Entity\TrainingRecord;
use App\Entity\Trainer;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use App\Repository\UploadRepository;
use App\Repository\ValidationRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\UserRepository;

use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;
use App\Service\TrainingRecordService;
use App\Service\PdfGeneratorService;
use App\Service\OperatorService;


class OperatorController extends AbstractController
{

    private $em;
    private $request;
    private $logger;
    private $authChecker;
    private $cache;

    // Repository methods
    private $validationRepository;
    private $uploadRepository;
    private $uapRepository;
    private $teamRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $trainerRepository;
    private $userRepository;

    // Services methods
    private $entitydeletionService;
    private $trainingRecordService;
    private $pdfGeneratorService;
    private $entityFetchingService;
    private $operatorService;



    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,
        RequestStack                    $requestStack,
        CacheInterface                  $cache,

        // Repository classes
        ValidationRepository            $validationRepository,
        UploadRepository                $uploadRepository,
        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository,
        UserRepository                  $userRepository,

        // Services classes
        EntityDeletionService           $entitydeletionService,
        TrainingRecordService           $trainingRecordService,
        PdfGeneratorService             $pdfGeneratorService,
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,

    ) {
        // $this->cache                        = $cache;

        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;
        $this->request                      = $requestStack->getCurrentRequest();
        $this->cache                        = $cache;

        // Variables related to the repositories
        $this->validationRepository         = $validationRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->uapRepository                = $uapRepository;
        $this->teamRepository               = $teamRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->trainerRepository            = $trainerRepository;
        $this->userRepository               = $userRepository;

        // Variables related to the services
        $this->entitydeletionService        = $entitydeletionService;
        $this->trainingRecordService        = $trainingRecordService;
        $this->pdfGeneratorService          = $pdfGeneratorService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
    }

    private function operatorEntitySearch(Request $request): array
    {
        $operators = [];


        // $this->logger->info(' the used method is a post');
        if ($request->getContentTypeFormat() == 'json') {
            // $this->logger->info('is the content type a json');
            $data = json_decode($request->getContent(), true);
            // $this->logger->info('data', $data);
            $name       = $data['search_name'];
            $code       = $data['search_code'];
            $team       = $data['search_team'];
            $uap        = $data['search_uap'];
            $trainer    = $data['search_trainer'];
        } else {
            // $this->logger->info('is the content type a form');
            $name       = $request->request->get('search_name');
            $code       = $request->request->get('search_code');
            $team       = $request->request->get('search_team');
            $uap        = $request->request->get('search_uap');
            $trainer    = $request->request->get('search_trainer');
        }
        $operators = $this->operatorRepository->findBySearchQuery($name, $code, $team, $uap, $trainer);


        return $operators;
    }


    #[Route('/operator/admin', name: 'app_operator')]
    public function operatorBasePage(Request $request): Response
    {
        // // $this->logger->info('search query with full request', $request->request->all());

        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator);
        $newOperatorForm->handleRequest($request);

        if ($newOperatorForm->isSubmitted() && $newOperatorForm->isValid()) {
            try {
                $newOperatorId = $this->processNewOperator($newOperator, $newOperatorForm, $request);
                $this->addFlash('success', 'L\'opérateur a bien été ajouté');
                // Generate the print detail URL
                $printUrl = $this->generateUrl('app_operator_detail', ['operatorId' => $newOperatorId]);

                // Store the print URL in the session
                $request->getSession()->set('print_operator_url', $printUrl);

                // Redirect to app_operator with a special parameter
                return $this->redirectToRoute('app_operator', ['open_print' => true]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être ajouté' . $e->getMessage());
                return $this->redirectToRoute('app_operator');
            }
        }

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorEntitySearch($request);
        } else {
            $operators = [];
        }

        $operatorForms = [];
        $this->logger->info('operators', [$operators]);

        // Create and handle forms
        foreach ($operators as $operator) {
            $this->logger->info('operator', [$operator->getUaps()->getValues()]);

            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ])->createView();
        }

        // $this->logger->info('in operatorBasePage is operatorForms empty: ' . count($operatorForms));

        if (count($operatorForms) === 0) {
            $inActiveOperators = $this->operatorRepository->findDeactivatedOperators();
            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }
        // $this->logger->info('message in flashbag', $flashes);

        return $this->render('services/operators/operators_admin.html.twig', [
            'newOperatorForm'   => $newOperatorForm->createView(),
            'operatorForms'     => $operatorForms,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
        ]);
    }




    private function processNewOperator(Operator $newOperator, $form, Request $request)
    {

        $trainerBool = $form->get('isTrainer')->getData();
        if ($trainerBool == true) {
            $trainer = new Trainer();
            $trainer->setOperator($newOperator);
            $trainer->setDemoted(false);
            $this->em->persist($trainer);
            $newOperator->setTrainer($trainer);
        } elseif ($trainerBool != true) {
            $trainer = $newOperator->getTrainer();
            $newOperator->setTrainer(null);
            if ($trainer != null) {
                $this->em->remove($trainer);
            }
        };
        $operator = $form->getData();
        $uaps = $operator->getUaps();
        foreach ($uaps as $uap) {
            $uap->addOperator($operator);
            $this->em->persist($uap);
        }
        $this->em->persist($operator);
        $this->em->flush();

        return $operator->getId();
    }





    // Individual operator modification controller, used in dev purpose
    #[Route('/operator/edit/{id}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, Operator $operator): Response
    {

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorEntitySearch($request);
            $operatorForms = [];
            $this->logger->info('operators', [$operators]);

            // Create and handle forms
            foreach ($operators as $operator) {
                $this->logger->info('operator', [$operator]);

                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
            $this->logger->info('operatorsForm', [$operatorForms]);
        }

        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $this->logger->info('operator after form creation', [$operator]);
        $this->logger->info('operator uaps after form creation', [$operator->getUaps()->getValues()]);

        $error = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operatorService->editOperatorService($form, $operator);
                $this->addFlash('success', 'L\'opérateur a bien été modifié');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être modifié. Erreur: ' . $e->getMessage());
                $this->logger->error('Error while editing operator in try catch', [$e->getMessage()]);
                $error = true;
            }
        } else {
            $this->logger->error('Error in submitting form while editing operator');
            $error = true;
        }

        $this->logger->info('operator', [$operator]);

        if ($error) {
            $this->logger->info('error true');
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'operatorForms' => [$operator->getId() => $form->createView()],
            ]);
        } elseif (isset($operatorForms)) {
            $this->logger->info('operatorsForms isset');
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'operatorForms' => $operatorForms,
            ]);
        } else {
            $this->logger->info('there is only operator', [[$operator], [$operator->getUaps()->getValues()]]);
            return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
                'form' => $form->createView(),
                'operator' => $operator,
                'operatorForms' => $operatorForms = [],

            ]);
        }
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
        $referer = $request->headers->get('referer');
        $validation = $this->validationRepository->find($validationId);
        $upload = $validation->getUpload();

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
        }
    }

    //first test of actual page rendering with a validated document and a dynamic form and list of operators and stuff
    #[Route('/operator/frontByUpl/{uploadId}', name: 'app_training_front_by_upload')]
    public function documentAndOperatorByUpload(Request $request, int $uploadId): Response
    {
        $referer = $request->headers->get('referer');
        $upload = $this->uploadRepository->find($uploadId);

        if ($request->getMethod() === 'GET') {
            return $this->render('services/operators/docAndOperator.html.twig', [
                'upload' => $upload,
            ]);
        } else {
            return $this->redirect($referer);
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
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
            'operators'         => $this->entityFetchingService->getOperators(),
        ]);
    }


    // Route to handle the newOperator form submission
    #[Route('/operator/traininglist/newOperator/{uploadId}/{teamId}/{uapId}', name: 'app_training_new_operator')]
    public function trainingListNewOperator(ValidatorInterface $validator, Request $request, int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {

        $team = $this->teamRepository->find($teamId);
        $uap = $this->uapRepository->find($uapId);
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
            // $this->logger->info('existingOperator', [$existingOperator->getName()]);
            if ($existingOperator->getTeam() == $team && $existingOperator->getUaps()->contains($uap)) {
                $this->addFlash('danger', 'Cet opérateur existe déjà dans cette equipe et uap');
                return $this->redirectToRoute('app_training_list', [
                    'uploadId' => $uploadId,
                    'teamId' => $teamId,
                    'uapId' => $uapId,
                ]);
            } else {
                $existingOperator->setTeam($team);
                $existingOperator->addUap($uap);
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
        $operator->addUap($uap);
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


            // $this->logger->info('danger', [$errorsString]);
            return $this->redirectToRoute('app_render_training_records', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }

        $this->em->persist($operator);
        $this->em->flush();
        $this->cache->delete('operators_list');
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
        // $this->logger->info('Full request', $request->request->all());

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

        $selectedOperators = $this->operatorRepository->findByTeamAndUap($teamId, $uapId);

        usort($selectedOperators, function ($a, $b) {
            list($firstNameA, $surnameA) = explode('.', $a->getName());
            list($firstNameB, $surnameB) = explode('.', $b->getName());

            return $surnameA === $surnameB ? strcmp($firstNameA, $firstNameB) : strcmp($surnameA, $surnameB);
        });

        $trainingRecords = []; // Array of training records
        $unorderedTrainingRecords = []; // Array of unordered training records
        $untrainedOperators = []; // Array of untrained operators
        $operatorsByTrainer = []; // Array of operators grouped by trainer
        $inTrainingOperatorsByTrainer = []; // Array of operators in training grouped by trainer

        foreach ($selectedOperators as $operator) {
            $records = $this->trainingRecordRepository->findBy(['operator' => $operator, 'upload' => $uploadId]);
            $unorderedTrainingRecords = array_merge($trainingRecords, $records);

            $record = $records[0] ?? null;
            if ($record) {
                // $this->logger->info('unorderedTrainingRecords', [$unorderedTrainingRecords]);
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

        // $this->logger->info('trainingRecords', [$trainingRecords]);

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
    public function trainingRecordFormManagement(int $uploadId, Request $request, ?int $teamId = null, ?int $uapId = null): Response
    {

        try {
            $this->trainingRecordService->trainingRecordTreatment($request);
        } catch (\Exception $e) {
            $this->logger->error('error during training record treatment', [$e]);
        } finally {
            return $this->redirectToRoute('app_render_training_records', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }
    }





    #[Route('/operator/check-duplicate-by-name', name: 'app_operator_check_duplicate_by_name', methods: ['POST'])]
    public function checkDuplicateOperatorByName(Request $request): JsonResponse
    {
        // $this->logger->info('Full requestbyname', [$request->request->all()]);

        $parsedRequest = json_decode($request->getContent(), true);
        // $this->logger->info('parsedRequest', [$parsedRequest]);

        $operatorName = $parsedRequest['value'];
        // $this->logger->info('operatorName', [$operatorName]);

        $existingOperator = $this->operatorRepository->findOneBy(['name' => $operatorName]);
        // $this->logger->info('existingOperator', [$existingOperator]);

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
            'found' => false,
            'field' => 'name',
            'value' => $operatorName,
            'message' => 'Aucun opérateur avec ce nom n\'existe'
        ]);
    }





    #[Route('/operator/check-duplicate-by-code', name: 'app_operator_check_duplicate_by_code', methods: ['POST'])]
    public function checkDuplicateOperatorByCode(Request $request): JsonResponse
    {
        // $this->logger->info('Full request bycode', $request->request->all());

        $parsedRequest = json_decode($request->getContent(), true);
        // $this->logger->info('parsedRequest', [$parsedRequest]);

        $operatorCode = $parsedRequest['value'];
        // $this->logger->info('operatorCode', [$operatorCode]);

        $existingOperator = $this->operatorRepository->findOneBy(['code' => $operatorCode]);
        // $this->logger->info('existingOperator', [$existingOperator]);

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
            'found' => false,
            'field' => 'code',
            'value' => $operatorCode,
            'message' => "Aucun opérateur avec ce codeOpé n'existe"
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

        $controllerOperator = $this->operatorRepository->findByCodeAndTeamAndUap($enteredCode, $teamId, $uapId);
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
                        'uap' => $operator->getUaps()->first()->getName(),
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

        // $this->logger->info('Full request', $parsedRequest);

        $enteredCode = $parsedRequest['code'];
        // $this->logger->info('enteredCode', [$enteredCode]);

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
            // $this->logger->info('enteredCode', [$enteredCode]);
        } else {
            $enteredCode = null;
        };

        if (key_exists('name', $parsedRequest)) {
            $enteredName = $parsedRequest['name'];
            // $this->logger->info('enteredName', [$enteredName]);
        } else {
            $enteredName = null;
        };

        if ($enteredCode != null) {
            $existingOperator = $this->operatorRepository->findOneBy(['code' => $enteredCode, 'name' => $enteredName, 'IsTrainer' => true]);
            if ($existingOperator !== null) {
                return new JsonResponse([
                    'found'         => true,
                    'name'          => $existingOperator->getName(),
                    'code'          => $existingOperator->getCode(),
                    'trainerId'     => $existingOperator->getId(),

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
        // $this->logger->info('app_suggest_names parsedRequest', $parsedRequest);

        $name = $parsedRequest['name'];

        /////////////// serialized data ////////////////////////
        $rawSuggestions = $this->operatorRepository->findByNameLikeForSuggestions($name);
        // $this->logger->info('app_suggest_names Raw suggestions', $rawSuggestions);

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

        // Serialize the entire array of entities at once using groups
        $serializedSuggestions = json_encode($rawSuggestions);

        // Since $serializedSuggestions is a JSON string, return it directly with JsonResponse
        return new JsonResponse($serializedSuggestions, 200, [], true);
    }



    // Route to print the operator detail in a pdf
    #[Route('/operator/detail/{operatorId}', name: 'app_operator_detail')]
    public function printOpeDetail(int $operatorId)
    {
        $operator = $this->operatorRepository->find($operatorId);
        // $this->logger->info('operator', [$operator]);

        // $pdfContent = $this->pdfGeneratorService->generateOperatorPdf($operator);
        $this->pdfGeneratorService->generateOperatorPdf($operator);

        return true;
    }



    #[Route('/operator/import', name: 'app_operator_import')]
    public function importOpe(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine)
    {
        /** @var EntityManagerInterface $em */
        $em = $doctrine->getManager();

        $unknownTeam = $this->teamRepository->findOneBy(['name' => 'INDEFINI']);
        $unknownUap = $this->uapRepository->findOneBy(['name' => 'INDEFINI']);
        if ($unknownTeam == null) {
            $unknownTeam = new Team();
            $unknownTeam->setName('INDEFINI');
            $em->persist($unknownTeam);
            $em->flush();
        }
        if ($unknownUap == null) {
            $unknownUap = new Uap();
            $unknownUap->setName('INDEFINI');
            $em->persist($unknownUap);
            $em->flush();
        }

        // Get all existing teams and UAPs
        $existingTeams = $this->teamRepository->findAll();
        $existingUaps = $this->uapRepository->findAll();

        // Handle the file upload
        $file = $request->files->get('operator-import-file');
        $ope_data = [];
        if ($file instanceof UploadedFile) {
            // Open the file
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                // Process the CSV data
                while (($data = fgetcsv($handle, 1000, ';', '"')) !== false) {
                    // Store $data in an array
                    $ope_data[] = $data;
                }
                // Close the file handle
                fclose($handle);
            } else {
                return new Response('Failed to open the file.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return new Response('No file uploaded or invalid file.', Response::HTTP_BAD_REQUEST);
        }

        // Begin a transaction
        $em->beginTransaction();
        try {
            // Process the data
            foreach ($ope_data as $data) {
                $code = $data[1];
                $firstname = $data[2];
                $surname = $data[3];
                $name = strtolower($firstname . '.' . $surname);

                // Find or default to 'INDEFINI' for team
                $team = $this->findEntityByName($existingTeams, $data[4], "INDEFINI");

                // Find or default to 'INDEFINI' for UAP
                $uap = $this->findEntityByName($existingUaps, $data[5], "INDEFINI");
                if ($uap->getName() === 'INDEFINI') {
                    $uap = $this->findEntityByName($existingUaps, $data[4], "INDEFINI");
                }

                $operator = new Operator();
                $operator->setCode($code);
                $operator->setName($name);
                $operator->setTeam($team);
                $operator->addUap($uap);

                // Validate the operator
                $errors = $validator->validate($operator);

                // Handle validation errors
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                    continue; // Skip this operator if there are validation errors
                }

                $suffix = 1;
                while (true) {
                    try {
                        $em->persist($operator);
                        $em->flush();

                        break; // Exit loop if successful
                    } catch (UniqueConstraintViolationException $e) {
                        // Modify the violating field and retry
                        $operator->setName($name . '_' . $suffix);
                        $suffix++;
                    }
                }
            }

            // Commit the transaction
            $em->commit();
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            $em->rollback();

            // Reset the EntityManager if it's closed
            if (!$em->isOpen()) {
                $em = $doctrine->resetManager();
            }
            $this->cache->delete('operators_list');

            // Re-throw the exception for further handling
            throw $e;
        }

        $this->addFlash('success', 'Les opérateurs ont bien été importés');
        return $this->redirectToRoute('app_operator');
    }




    /**
     * Helper function to find an entity by name or return a default.
     *
     * @param array  $entities
     * @param string $name
     * @param string $defaultName
     *
     * @return object
     */
    private function findEntityByName(array $entities, string $name, string $defaultName)
    {
        foreach ($entities as $entity) {
            if ($entity->getName() === $name) {
                return $entity;
            }
        }

        // Return the entity with the default name
        foreach ($entities as $entity) {
            if ($entity->getName() === $defaultName) {
                return $entity;
            }
        }

        throw new \Exception('Default entity not found');
    }




    // Route to delete UAP or Team without breaking operators and training records database
    #[Route('/operator/delete-uap-or-team/{entityType}/{entityId}', name: 'app_delete_uap_or_team')]
    public function deleteUapTeamProperly(string $entityType, int $entityId, Request $request): Response
    {
        $originUrl = $request->headers->get('referer');

        if (!$this->authChecker->IsGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action');
            return $this->redirect($originUrl);
        }
        try {
            $this->entitydeletionService->deleteEntity($entityType, $entityId);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la suppression de l\'entité' . $e->getMessage());
            return $this->redirectToRoute('app_operator');
        }
        return $this->redirect($originUrl);
    }



    #[Route('/operator/operator_management_base_page', name: 'app_operator_team_or_uap_management')]
    public function operatorManagement(Request $request): Response
    {
        $teams = $this->teamRepository->findAll();
        $uaps = $this->uapRepository->findAll();

        if (count($teams) == 0 || count($uaps) == 0) {
            $team = new Team();
            $uap = new Uap();
            $team->setName('INDEFINI');
            $uap->setName('INDEFINI');
            $this->em->persist($team);
            $this->em->persist($uap);
            $this->em->flush();
        }

        $team = new Team();
        $uap = new Uap();
        $teamForm = $this->createForm(TeamType::class, $team);
        $uapForm = $this->createForm(UapType::class, $uap);

        $originUrl = $this->request->headers->get('referer');

        if ($request->getMethod() == 'POST') {
            if (!$this->authChecker->IsGranted('ROLE_ADMIN')) {
                $this->addFlash('danger', 'Vous n\'avez pas les droits pour effectuer cette action');
                return $this->redirect($originUrl);
            }
            $teamForm->handleRequest($request);
            $uapForm->handleRequest($request);
            if ($teamForm->isSubmitted()) {
                if ($teamForm->isValid()) {
                    $team = $teamForm->getData();
                    $this->em->persist($team);
                    $this->em->flush();
                    $this->addFlash('success', 'team has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageTeam = $teamForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageTeam);
                    return $this->redirect($originUrl);
                }
            }
            if ($uapForm->isSubmitted()) {
                if ($uapForm->isValid()) {
                    $uap = $uapForm->getData();
                    $this->em->persist($uap);
                    $this->em->flush();
                    $this->addFlash('success', 'Uap has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageUap = $uapForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageUap);
                    return $this->redirect($originUrl);
                }
            }
        } elseif ($request->getMethod() == 'GET') {
            return $this->render('services/operators/team_uap_operator_management.html.twig', [
                'teams'     => $teams,
                'uaps'      => $uaps,
                'teamForm'  => $teamForm->createView(),
                'uapForm'   => $uapForm->createView()
            ]);
        }
    }



    #[Route('/operator/user_login_check', name: 'app_operator_user_login_check')]
    public function userLoginCheck(): JsonResponse
    {
        $currentUser = $this->getUser();
        $this->logger->info('current user', [$currentUser]);
        $this->logger->info('role granted', [$this->authChecker->isGranted('ROLE_MANAGER')]);

        if (!empty($currentUser) && $this->authChecker->isGranted('ROLE_MANAGER')) {
            $user               = $this->userRepository->find($currentUser);
            $this->logger->info(' user', [$user]);

            $operator           = $this->operatorRepository->findOneBy(['name' => $user->getUsername()]);
            $this->logger->info('operator', [$operator]);

            if ($operator != null && $operator->isIsTrainer()) {
                return new JsonResponse([
                    'found'         => true,
                    'name'          => $operator->getName(),
                    'code'          => $operator->getCode(),
                    'trainerId'     => $operator->getId(),
                    'uploadTrainer' => true,
                ]);
            }
        }
        return new JsonResponse([
            'found'         => false
        ]);
    }
}
