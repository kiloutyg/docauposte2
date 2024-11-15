<?php

namespace App\Controller;

use  \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
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

// use App\Entity\Zone;
// use App\Entity\ProductLine;
// use App\Entity\User;
// use App\Entity\Upload;
// use App\Entity\Category;
// use App\Entity\Button;
// use App\Entity\Signature;
// use App\Entity\Incident;
// use App\Entity\IncidentCategory;
// use App\Entity\Service;

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
use App\Service\CacheService;
use App\Service\PdfGeneratorService;
use App\Service\SettingsService;

#[Route('/', name: 'app_')]

# This controller is extended to make it easier to access routes

class BaseController extends AbstractController
{

    private   $cache;

    protected $em;
    protected $request;
    protected $security;
    protected $passwordHasher;
    protected $requestStack;
    protected $session;
    protected $logger;
    protected $loggerInterface;
    protected $projectDir;
    protected $public_dir;
    protected $authChecker;

    // Repository methods
    protected $departmentRepository;
    protected $approbationRepository;
    protected $validationRepository;
    protected $incidentRepository;
    protected $incidentCategoryRepository;
    protected $categoryRepository;
    protected $buttonRepository;
    protected $uploadRepository;
    protected $zoneRepository;
    protected $productLineRepository;
    protected $userRepository;
    protected $oldUploadRepository;
    protected $uapRepository;
    protected $teamRepository;
    protected $operatorRepository;
    protected $trainingRecordRepository;
    protected $trainerRepository;
    protected $settingsRepository;
    // Services methods
    protected $validationService;
    protected $incidentService;
    protected $folderCreationService;
    protected $entityHeritanceService;
    protected $mailerService;
    protected $entitydeletionService;
    protected $accountService;
    protected $uploadService;
    protected $oldUploadService;
    protected $viewsModificationService;
    protected $trainingRecordService;
    protected $operatorService;
    protected $cacheService;
    protected $pdfGeneratorService;
    protected $settingsService;
    // Variables used in the twig templates to display all the entities
    protected $departments;
    protected $zones;
    protected $productLines;
    protected $users;
    protected $uploads;
    protected $categories;
    protected $buttons;
    protected $incidents;
    protected $incidentCategories;
    protected $validations;
    protected $teams;
    protected $operators;
    protected $uaps;
    protected $approbations;
    protected $oldUploads;
    protected $trainingRecords;
    protected $trainers;
    protected $settings;

    
    public function __construct(

        TagAwareCacheInterface          $cache,

        EntityManagerInterface          $em,
        RequestStack                    $requestStack,
        Security                        $security,
        UserPasswordHasherInterface     $passwordHasher,
        LoggerInterface                 $loggerInterface,
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
        CacheService                    $cacheService,
        PdfGeneratorService             $pdfGeneratorService,
        SettingsService                 $settingsService

    ) {
        $this->cache                        = $cache;

        $this->em                           = $em;
        $this->requestStack                 = $requestStack;
        $this->security                     = $security;
        $this->passwordHasher               = $passwordHasher;
        $this->logger                       = $loggerInterface;
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
        $this->cacheService                 = $cacheService;
        $this->pdfGeneratorService          = $pdfGeneratorService;
        $this->settingsService              = $settingsService;

        // $this->cachingAppVariableAsArray();
        $this->cacheService->cachingAppVariable();
        // $this->cachingSettings();
    }

    // public function cachingSettings()
    // {
    //     $this->cacheService->settings = $this->cache->get("settings_cache_base", function (ItemInterface $item) {
    //         $item->tag("settings_tag_base");
    //         $item->expiresAfter(43200);
    //         // $item->expiresAfter(60);
    //         return $this->settingsRepository->getSettings();
    //     });
    // }

    // public function cachingAppVariableAsArray()
    // {
    //     $variables = [
    //         'zones' => fn() => $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']),
    //         'productLines' => fn() => $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']),
    //         'categories' => fn() => $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']),
    //         'buttons' => fn() => $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']),
    //         'users' => fn() => $this->userRepository->findAll(),
    //         'uploads' => fn() => $this->uploadRepository->findAll(),
    //         'incidents' => fn() => $this->incidentRepository->findAll(),
    //         'incidentCategories' => fn() => $this->incidentCategoryRepository->findAll(),
    //         'departments' => fn() => $this->departmentRepository->findAll(),
    //         'validations' => fn() => $this->validationRepository->findAll(),
    //         'teams' => fn() => $this->teamRepository->findAll(),
    //         'uaps' => fn() => $this->uapRepository->findAll(),
    //         'operators' => fn() => $this->operatorRepository->findAllOrdered(),
    //         'approbations' => fn() => $this->approbationRepository->findAll(),
    //         'trainingRecords' => fn() => $this->trainingRecordRepository->findAll(),
    //         'trainers' => fn() => $this->trainerRepository->findAll(),
    //     ];

    //     foreach ($variables as $key => $value) {
    //         try {
    //             $this->$key = $this->cache->get("{$key}_cache_array", function (ItemInterface $item) use ($value, $key) {
    //                 $item->tag(["{$key}_tag_array"]);
    //                 $item->expiresAfter(43200); // Cache for 12 hours
    //                 return $value();
    //             });
    //         } catch (\Exception $e) {
    //             $this->logger->error("Error caching {$key}: " . $e->getMessage());
    //         }
    //     }
    // }


    // public function clearAndRebuildCachesArrays()
    // {
    //     // Clear the cache
    //     foreach (['zones', 'productLines', 'categories', 'buttons', 'uploads', 'incidents', 'incidentCategories', 'departments', 'validations', 'teams', 'operators', 'uaps', 'approbations'] as $key) {
    //         $this->cache->delete("{$key}_cache_array");
    //     }
    //     $this->cachingAppVariableAsArray();

    //     $this->cache->delete("settings_cache_base");
    //     $this->cachingSettings();
    // }


    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $commonParameters = [
            'zones'                 => $this->cacheService->zones,
            'productLines'          => $this->cacheService->productLines,
            'categories'            => $this->cacheService->categories,
            'buttons'               => $this->cacheService->buttons,
            'uploads'               => $this->cacheService->uploads,
            'users'                 => $this->cacheService->users,
            'incidents'             => $this->cacheService->incidents,
            'incidentCategories'    => $this->cacheService->incidentCategories,
            'departments'           => $this->cacheService->departments,
            'validations'           => $this->cacheService->validations,
            'teams'                 => $this->cacheService->teams,
            'operators'             => $this->cacheService->operators,
            'uaps'                  => $this->cacheService->uaps,
            'approbations'          => $this->cacheService->approbations,
            'trainingRecords'       => $this->cacheService->trainingRecords,
            'trainers'              => $this->cacheService->trainers,
            'settings'              => $this->cacheService->settings,
        ];



        $parameters = array_merge($commonParameters, $parameters);

        return parent::render($view, $parameters, $response);
    }
}
