<?php

namespace App\Controller;

use  \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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



    public function __construct(

        CacheInterface                  $cache,

        EntityManagerInterface          $em,
        RequestStack                    $requestStack,
        Security                        $security,
        UserPasswordHasherInterface     $passwordHasher,
        LoggerInterface                 $loggerInterface,
        ParameterBagInterface           $params,
        IncidentRepository              $incidentRepository,
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
        OperatorService                 $operatorService

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

        // Variables used in the twig templates to display all the entities
        // $this->zones                        = $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']);
        // $this->productLines                 = $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']);
        // $this->categories                   = $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']);
        // $this->buttons                      = $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']);
        // $this->users                        = $this->userRepository->findAll();
        // $this->uploads                      = $this->uploadRepository->findAll();
        // $this->incidents                    = $this->incidentRepository->findAll();
        // $this->incidentCategories           = $this->incidentCategoryRepository->findAll();
        // $this->departments                  = $this->departmentRepository->findAll();
        // $this->validations                  = $this->validationRepository->findAll();
        // $this->teams                        = $this->teamRepository->findAll();
        // $this->operators                    = $this->operatorRepository->findAllOrdered();
        // $this->uaps                         = $this->uapRepository->findAll();
        $this->cachingAppVariable();
    }

    // public function cachingAppVariable()
    // {
    //     $zones = $this->cache->get('zones_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']);
    //     });
    //     $this->zones = $zones;

    //     $productLines = $this->cache->get('productLines_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']);
    //     });
    //     $this->productLines = $productLines;

    //     $categories = $this->cache->get('categories_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']);
    //     });
    //     $this->categories = $categories;

    //     $buttons = $this->cache->get('buttons_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']);
    //     });
    //     $this->buttons = $buttons;

    //     $users = $this->cache->get('users_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->userRepository->findAll();
    //     });
    //     $this->users = $users;

    //     $uploads = $this->cache->get('uploads_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->uploadRepository->findAll();
    //     });
    //     $this->uploads = $uploads;

    //     $incidents = $this->cache->get('incidents_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->incidentRepository->findAll();
    //     });
    //     $this->incidents = $incidents;

    //     $incidentCategories = $this->cache->get('incidentCategories_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->incidentCategoryRepository->findAll();
    //     });
    //     $this->incidentCategories = $incidentCategories;

    //     $departments = $this->cache->get('departments_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->departmentRepository->findAll();
    //     });
    //     $this->departments = $departments;

    //     $validations = $this->cache->get('validations_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->validationRepository->findAll();
    //     });
    //     $this->validations = $validations;

    //     $teams = $this->cache->get('teams_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->teamRepository->findAll();
    //     });
    //     $this->teams = $teams;

    //     $uaps = $this->cache->get('uaps_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->uapRepository->findAll();
    //     });
    //     $this->uaps = $uaps;

    //     $operators = $this->cache->get('operators_cache', function (ItemInterface $item) {
    //         $item->expiresAfter(3600); // Cache for 1 hour
    //         return $this->operatorRepository->findAllOrdered();
    //     });
    //     $this->operators = $operators;


    //     return $this;
    // }
    public function cachingAppVariable()
    {
        $variables = [
            'zones' => fn () => $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']),
            'productLines' => fn () => $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']),
            'categories' => fn () => $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']),
            'buttons' => fn () => $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']),
            'users' => fn () => $this->userRepository->findAll(),
            'uploads' => fn () => $this->uploadRepository->findAll(),
            'incidents' => fn () => $this->incidentRepository->findAll(),
            'incidentCategories' => fn () => $this->incidentCategoryRepository->findAll(),
            'departments' => fn () => $this->departmentRepository->findAll(),
            'validations' => fn () => $this->validationRepository->findAll(),
            'teams' => fn () => $this->teamRepository->findAll(),
            'uaps' => fn () => $this->uapRepository->findAll(),
            'operators' => fn () => $this->operatorRepository->findAllOrdered()
        ];

        foreach ($variables as $key => $value) {
            try {
                $this->$key = $this->cache->get("{$key}_cache", function (ItemInterface $item) use ($value) {
                    $item->expiresAfter(3600); // Cache for 1 hour
                    return $value();
                });
            } catch (\Exception $e) {
                $this->logger->error("Error caching {$key}: " . $e->getMessage());
            }
        }
    }
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $commonParameters = [
            'zones'                 => $this->zones,
            'productLines'          => $this->productLines,
            'categories'            => $this->categories,
            'buttons'               => $this->buttons,
            'uploads'               => $this->uploads,
            'users'                 => $this->users,
            'incidents'             => $this->incidents,
            'incidentCategories'    => $this->incidentCategories,
            'departments'           => $this->departments,
            'validations'           => $this->validations,
            'teams'                 => $this->teams,
            'operators'             => $this->operators,
            'uaps'                  => $this->uaps
        ];

        $parameters = array_merge($commonParameters, $parameters);

        return parent::render($view, $parameters, $response);
    }
}
