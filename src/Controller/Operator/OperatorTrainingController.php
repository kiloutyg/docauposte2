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


class OperatorTrainingController extends OperatorBaseController
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
}
