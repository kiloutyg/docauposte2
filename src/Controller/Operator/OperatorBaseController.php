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


class OperatorBaseController extends OperatorBaseController
{

    public $em;
    public $request;
    public $logger;
    public $authChecker;
    public $cache;

    // Repository methods
    public $validationRepository;
    public $uploadRepository;
    public $uapRepository;
    public $teamRepository;
    public $operatorRepository;
    public $trainingRecordRepository;
    public $trainerRepository;
    public $userRepository;

    // Services methods
    public $entitydeletionService;
    public $trainingRecordService;
    public $pdfGeneratorService;
    public $entityFetchingService;
    public $operatorService;



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

    public function operatorEntitySearch(Request $request): array
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
    public function operatorAdminPage(Request $request): Response
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




    public function processNewOperator(Operator $newOperator, $form, Request $request)
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






    /**
     * Helper function to find an entity by name or return a default.
     *
     * @param array  $entities
     * @param string $name
     * @param string $defaultName
     *
     * @return object
     */

    public function findEntityByName(array $entities, string $name, string $defaultName)
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
}
