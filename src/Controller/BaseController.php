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
use App\Service\UploadsService;
use App\Service\FolderCreationService;
use App\Service\IncidentsService;
use App\Service\EntityHeritanceService;
use App\Service\ValidationService;


#[Route('/', name: 'app_')]

# This controller is extended to make it easier to access routes

class BaseController extends AbstractController
{
    protected $zoneRepository;
    protected $productLineRepository;
    protected $userRepository;
    protected $em;
    protected $request;
    protected $security;
    protected $passwordHasher;
    protected $requestStack;
    protected $uploadRepository;
    protected $session;
    protected $categoryRepository;
    protected $buttonRepository;
    protected $entitydeletionService;
    protected $accountService;
    protected $uploadsService;
    protected $logger;
    protected $loggerInterface;
    protected $projectDir;
    protected $public_dir;
    protected $folderCreationService;
    protected $incidentRepository;
    protected $incidentsService;
    protected $incidentCategoryRepository;
    protected $entityHeritanceService;
    protected $authChecker;
    protected $departmentRepository;
    protected $validationService;

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



    public function __construct(
        UploadRepository                $uploadRepository,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        UserRepository                  $userRepository,
        EntityManagerInterface          $em,
        RequestStack                    $requestStack,
        Security                        $security,
        UserPasswordHasherInterface     $passwordHasher,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        EntityDeletionService           $entitydeletionService,
        AccountService                  $accountService,
        UploadsService                  $uploadsServices,
        LoggerInterface                 $loggerInterface,
        ParameterBagInterface           $params,
        FolderCreationService           $folderCreationService,
        IncidentRepository              $incidentRepository,
        IncidentsService                $incidentsService,
        IncidentCategoryRepository      $incidentCategoryRepository,
        EntityHeritanceService          $entityHeritanceService,
        AuthorizationCheckerInterface   $authChecker,
        DepartmentRepository            $departmentRepository,
        ValidationService               $validationService

    ) {

        $this->uploadRepository             = $uploadRepository;
        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->userRepository               = $userRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->em                           = $em;
        $this->requestStack                 = $requestStack;
        $this->security                     = $security;
        $this->passwordHasher               = $passwordHasher;
        $this->entitydeletionService        = $entitydeletionService;
        $this->accountService               = $accountService;
        $this->uploadsService               = $uploadsServices;
        $this->logger                       = $loggerInterface;
        $this->request                      = $this->requestStack->getCurrentRequest();
        $this->session                      = $this->requestStack->getSession();
        $this->projectDir                   = $params->get('kernel.project_dir');
        $this->public_dir                   = $this->projectDir . '/public';
        $this->folderCreationService        = $folderCreationService;
        $this->incidentRepository           = $incidentRepository;
        $this->incidentsService             = $incidentsService;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->entityHeritanceService       = $entityHeritanceService;
        $this->authChecker                  = $authChecker;
        $this->departmentRepository         = $departmentRepository;
        $this->validationService            = $validationService;
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
        ];

        $parameters = array_merge($commonParameters, $parameters);

        return parent::render($view, $parameters, $response);
    }
}