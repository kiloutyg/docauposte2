<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Doctrine\ORM\EntityManagerInterface;

// use  \Psr\Log\LoggerInterface;

use App\Entity\ProductLine;
use App\Entity\Category;
use App\Entity\Button;
use App\Entity\Department;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\IncidentRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;

use App\Service\EntityFetchingService;
use App\Service\AccountService;
use App\Service\SettingsService;
use App\Service\ValidationService;
use App\Service\OperatorService;

// This controller manage the logic of the front interface, it is the main controller of the application and is responsible for rendering the front interface.
// It is also responsible for creating the super-admin account.

#[Route('/', name: 'app_')]
class FrontController extends AbstractController
{

    private $em;
    private $authChecker;
    // private $logger;

    private $categoryRepository;
    private $buttonRepository;
    private $incidentRepository;
    private $zoneRepository;
    private $productLineRepository;

    private $settingsService;
    private $validationService;
    private $operatorService;
    private $entityFetchingService;
    private $accountService;



    public function __construct(

        AuthorizationCheckerInterface   $authChecker,
        EntityManagerInterface          $em,
        // LoggerInterface                 $logger,

        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        IncidentRepository              $incidentRepository,


        SettingsService                 $settingsService,
        OperatorService                 $operatorService,
        ValidationService               $validationService,
        EntityFetchingService           $entityFetchingService,
        AccountService                  $accountService,

    ) {
        $this->authChecker                  = $authChecker;
        $this->em                           = $em;
        // $this->logger                       = $logger;

        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;


        $this->operatorService              = $operatorService;
        $this->settingsService              = $settingsService;
        $this->validationService            = $validationService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->accountService               = $accountService;
    }



    // This function is responsible for creating the super-admin account at the first connection of the application.
    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(Request $request): Response
    {
        $users = [];
        $users  = $this->entityFetchingService->getUsers();

        if ($users == null) {

            $error = null;
            $result = $this->accountService->createAccount(
                $request,
                $error
            );
            if ($result) {
                $this->addFlash('success', 'Le compte de Super-Administrateur a bien été créé.');
            }
            if ($error) {
                $this->addFlash('error', $error);
            }
        } else {
            $this->addFlash('alert', 'Le compte de Super-Administrateur existe déjà.');
            return $this->redirectToRoute('app_base');
        }
        return $this->redirectToRoute('app_base');
    }



    // Render the base page
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        $users = $this->entityFetchingService->getUsers();
        $settings = $this->settingsService->getSettings();
        if ($settings->isUploadValidation() && $this->entityFetchingService->getValidations() != null) {
            $this->validationService->remindCheck($users);
        }

        if ($this->entityFetchingService->getDepartments() == null) {
            $department = new Department();
            $department->setName('I.T.');
            $this->em->persist($department);
            $department = new Department();
            $department->setName('QUALITY');
            $this->em->persist($department);
            $this->em->flush();
        }

        if ($settings->isTraining() && $this->authChecker->isGranted('ROLE_MANAGER')) {
            $countArray = $this->operatorService->operatorCheckForAutoDelete();
            if ($countArray != null) {
                $this->addFlash('info', ($countArray['findDeactivatedOperators'] === 1 ? $countArray['findDeactivatedOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['findDeactivatedOperators'] . ' opérateurs inactifs sont à supprimer. ') .
                    ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
            }
        }

        return $this->render(
            'base.html.twig',
            [
                'zones'                 => $this->entityFetchingService->getZones(),
            ]
        );
    }




    // Render the zone page
    #[Route('/zone/{zoneId}', name: 'zone')]
    public function zone(
        int $zoneId = null,
        // Request $request = null
    ): Response {
        // // $this->logger->info('zoneId', [$zoneId]);
        // $this->logger->info('request in frontController', [$request]);
        // $this->logger->info('frontController request request all', [$request->request->all()]);
        // $this->logger->info('frontController request attributes all', [$request->attributes->all()]);
        // $routeName = $request->attributes->get('_route');
        // $this->logger->info('routeName in frontController', [$routeName]);

        // $routeParams =  $request->attributes->get('_route_params');
        // $this->logger->info('routeParam in frontController', [$routeParams]);

        $zone = $this->zoneRepository->find($zoneId);

        $productLinesInZone = [];
        $productLinesInZone = $zone->getProductLines();
        if (count($productLinesInZone) === 1 && !$this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->productLine(null, $productLinesInZone[0]);
        } else {
            return $this->render(
                'zone.html.twig',
                [
                    'zone' => $zone,
                    'productLines' => $productLinesInZone
                ]
            );
        }
    }


    // Render the productline page and redirect to the mandatory incident page if there is one
    #[Route('/productline/{productLineId}', name: 'productLine')]
    public function productLine(int $productLineId = null, ProductLine $productLine = null): Response
    {
        // $this->logger->info('productLine and productLineID', ['productLineId' => $productLineId, 'productLine' => $productLine]);

        if (!$productLine) {
            $productLine = $this->productLineRepository->find($productLineId);
        }

        $categoriesInLine = $productLine->getCategories();
        // $this->logger->info('categoriesInLine', [$categoriesInLine]);
        $incidents = [];
        $incidents = $this->incidentRepository->findBy(
            ['productLine' => $productLineId],
            ['id' => 'ASC'] // order by id ascending
        );
        // $this->logger->info('incidents', [$incidents]);

        $incidentId = count($incidents) > 0 ? $incidents[0]->getId() : null;

        if (count($incidents) === 0) {
            if (count($categoriesInLine) === 1 && !$this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
                return $this->category(null, $categoriesInLine[0]);
            }
            return $this->render(
                'productline.html.twig',
                [
                    'productLine' => $productLine,
                    'categories' => $categoriesInLine
                ]
            );
        } else {
            return $this->redirectToRoute('app_mandatory_incident', [
                'productLineId' => $productLine->getid(),
                'incidentId' => $incidentId
            ]);
        }
    }




    // Render the category page and redirect to the button page if there is only one button in the category
    #[Route('/category/{categoryId}', name: 'category')]
    public function category(int $categoryId = null, Category $category = null): Response
    {
        // $this->logger->info('category and categoryId', ['categoryId' => $categoryId, 'category' => $category]);

        $buttons = [];
        if (!$category) {
            $category = $this->categoryRepository->find($categoryId);
        }

        $buttons = $category->getButtons();

        if (count($buttons) === 1) {
            return $this->buttonDisplay(null, $buttons[0]);
        } else {
            return $this->render(
                'category.html.twig',
                [
                    'category'    => $category,
                    'buttons' => $buttons,
                ]
            );
        }
    }




    // Render the button page and redirect to the upload page if there is only one upload in the button
    #[Route('/button/{buttonId}', name: 'button')]
    public function buttonDisplay(int $buttonId = null, Button $button = null): Response
    {
        if (!$button) {
            $button = $this->buttonRepository->find($buttonId);
        }

        $buttonUploads = $button->getUploads();

        if (count($buttonUploads) != 1) {
            return $this->render(
                'button.html.twig',
                [
                    'button'      => $button,
                    'uploads'     => $buttonUploads,
                ]
            );
        } else {
            $uploadId = $buttonUploads[0]->getId();
            return $this->redirectToRoute('app_download_file', [
                'uploadId' => $uploadId
            ]);
        }
    }
}
