<?php

namespace App\Controller;

use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;


use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RequestStack;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ValidationRepository;
use App\Repository\ApprobationRepository;
use App\Repository\OldUploadRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\SettingsRepository;

use App\Service\EntityDeletionService;
use App\Service\AccountService;
use App\Service\UploadService;
use App\Service\FolderCreationService;
use App\Service\IncidentService;
use App\Service\EntityHeritanceService;
use App\Service\ValidationService;
use App\Service\MailerService;
use App\Service\OldUploadService;
use App\Service\ViewsModificationService;
use App\Service\TrainingRecordService;
use App\Service\OperatorService;
use App\Service\PdfGeneratorService;
use App\Service\SettingsService;
use App\Service\EntityFetchingService;
use App\Service\ErrorService;

#[Route('/', name: 'app_')]

class BaseController extends AbstractController
{


    private $em;
    private $request;
    private $security;
    private $passwordHasher;
    private $requestStack;
    private $session;
    private $logger;
    private $projectDir;
    private $public_dir;
    private $authChecker;

    // Repository methods
    private $departmentRepository;
    private $approbationRepository;
    private $validationRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $zoneRepository;
    private $productLineRepository;
    private $userRepository;
    private $oldUploadRepository;
    private $uapRepository;
    private $teamRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $trainerRepository;
    private $settingsRepository;


    // Services methods
    private $validationService;
    private $incidentService;
    private $folderCreationService;
    private $entityHeritanceService;
    private $mailerService;
    private $entitydeletionService;
    private $accountService;
    private $uploadService;
    private $oldUploadService;
    private $viewsModificationService;
    private $trainingRecordService;
    private $operatorService;
    private $pdfGeneratorService;
    private $settingsService;
    private $entityFetchingService;
    private $errorService;




    private function __construct(

        EntityManagerInterface          $em,
        RequestStack                    $requestStack,
        Security                        $security,
        UserPasswordHasherInterface     $passwordHasher,
        LoggerInterface                 $logger,
        ParameterBagInterface           $params,
        AuthorizationCheckerInterface   $authChecker,

        // Repository methods
        ApprobationRepository           $approbationRepository,
        ValidationRepository            $validationRepository,
        DepartmentRepository            $departmentRepository,
        IncidentCategoryRepository      $incidentCategoryRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        UploadRepository                $uploadRepository,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        UserRepository                  $userRepository,
        OldUploadRepository             $oldUploadRepository,
        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository,
        SettingsRepository              $settingsRepository,
        IncidentRepository              $incidentRepository,


        // Services methods
        ValidationService               $validationService,
        IncidentService                 $incidentService,
        EntityHeritanceService          $entityHeritanceService,
        FolderCreationService           $folderCreationService,
        EntityDeletionService           $entitydeletionService,
        AccountService                  $accountService,
        UploadService                   $uploadService,
        MailerService                   $mailerService,
        OldUploadService                $oldUploadService,
        ViewsModificationService        $viewsModificationService,
        TrainingRecordService           $trainingRecordService,
        OperatorService                 $operatorService,
        PdfGeneratorService             $pdfGeneratorService,
        SettingsService                 $settingsService,
        EntityFetchingService           $entityFetchingService,
        ErrorService                    $errorService,

    ) {
        $this->em                           = $em;
        $this->requestStack                 = $requestStack;
        $this->security                     = $security;
        $this->passwordHasher               = $passwordHasher;
        $this->logger                       = $logger;
        $this->request                      = $this->requestStack->getCurrentRequest();
        $this->session                      = $this->requestStack->getSession();
        $this->projectDir                   = $params->get('kernel.project_dir');
        $this->public_dir                   = $this->projectDir . '/public';
        $this->authChecker                  = $authChecker;

        // Variables related to the repositories
        $this->departmentRepository         = $departmentRepository;
        $this->approbationRepository        = $approbationRepository;
        $this->validationRepository         = $validationRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->userRepository               = $userRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->oldUploadRepository          = $oldUploadRepository;
        $this->uapRepository                = $uapRepository;
        $this->teamRepository               = $teamRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->trainerRepository            = $trainerRepository;
        $this->settingsRepository           = $settingsRepository;

        // Variables related to the services
        $this->mailerService                = $mailerService;
        $this->oldUploadService             = $oldUploadService;
        $this->validationService            = $validationService;
        $this->incidentService              = $incidentService;
        $this->entityHeritanceService       = $entityHeritanceService;
        $this->folderCreationService        = $folderCreationService;
        $this->uploadService                = $uploadService;
        $this->accountService               = $accountService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->viewsModificationService     = $viewsModificationService;
        $this->trainingRecordService        = $trainingRecordService;
        $this->operatorService              = $operatorService;
        $this->pdfGeneratorService          = $pdfGeneratorService;
        $this->settingsService              = $settingsService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->errorService                 = $errorService;
    }
}
