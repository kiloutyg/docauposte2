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


class OperatorCheckersController extends OperatorBaseController
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
        }

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
}
