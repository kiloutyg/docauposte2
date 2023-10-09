<?php

namespace App\Controller;

use  \Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\HttpFoundation\Response;

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


#[Route('/', name: 'app_')]

# This controller is extended to make it easier to access routes

class BaseController extends AbstractController
{
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



    public function __construct(

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

        // Services methods
        ValidationService               $validationService,
        IncidentService                 $incidentService,
        EntityHeritanceService          $entityHeritanceService,
        FolderCreationService           $folderCreationService,
        EntityDeletionService           $entitydeletionService,
        AccountService                  $accountService,
        UploadService                   $uploadService,
        MailerService                   $mailerService,
        OldUploadService                $oldUploadService

    ) {

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


        // Variables used in the twig templates to display all the entities
        $this->zones                        = $this->zoneRepository->findAll();
        $this->productLines                 = $this->productLineRepository->findAll();
        $this->users                        = $this->userRepository->findAll();
        $this->uploads                      = $this->uploadRepository->findAll();
        $this->categories                   = $this->categoryRepository->findAll();
        $this->buttons                      = $this->buttonRepository->findAll();
        $this->incidents                    = $this->incidentRepository->findAll();
        $this->incidentCategories           = $this->incidentCategoryRepository->findAll();
        $this->departments                  = $this->departmentRepository->findAll();
        $this->validations                  = $this->validationRepository->findAll();
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
        ];

        $parameters = array_merge($commonParameters, $parameters);

        return parent::render($view, $parameters, $response);
    }
}