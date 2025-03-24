<?php

namespace App\Controller;

// use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Category;
use App\Entity\Button;

use App\Repository\ZoneRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;

use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\FolderService;
use App\Service\IncidentService;
use App\Service\EntityHeritanceService;
use App\Service\ErrorService;

#[Route('/category_admin', name: 'app_category_')]
class CategoryAdminController extends AbstractController
{

    private $em;
    // private $logger;
    private $authChecker;

    // Repository methods
    private $categoryRepository;
    private $buttonRepository;
    private $zoneRepository;

    // Services methods
    private $incidentService;
    private $folderService;
    private $entityHeritanceService;
    private $entitydeletionService;
    private $uploadService;
    private $errorService;

    public function __construct(

        EntityManagerInterface          $em,
        // LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        // Repository methods
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        ZoneRepository                  $zoneRepository,


        // Services methods
        IncidentService                 $incidentService,
        EntityHeritanceService          $entityHeritanceService,
        FolderService           $folderService,
        EntityDeletionService           $entitydeletionService,
        UploadService                   $uploadService,
        ErrorService                    $errorService,

    ) {
        $this->em                           = $em;
        // $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        // Variables related to the repositories
        $this->zoneRepository               = $zoneRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;

        // Variables related to the services
        $this->incidentService              = $incidentService;
        $this->entityHeritanceService       = $entityHeritanceService;
        $this->folderService        = $folderService;
        $this->uploadService                = $uploadService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->errorService                 = $errorService;
    }

    #[Route('/{categoryId}', name: 'admin')]
    // This function is responsible for rendering the category's admin interface. 
    public function categoryAdmin(?int $categoryId = null, ?Category $category = null): Response
    {
        $pageLevel = 'category';
        if ($category === null) {
            $category = $this->categoryRepository->find($categoryId);
        }
        if (!$category) {
            $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $categoryButtons        = $category->getButtons();
        $productLine            = $category->getProductLine();
        $zone                   = $productLine->getZone();
        $productLineCategories  = $productLine->getCategories();

        // These functions are responsible for retrieving the uploads and incidents children of the current category, it depends on the EntityHeritanceService class.
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'category',
            $category
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'category',
            $category
        );

        // These functions are responsible for grouping the uploads and incidents by button and parent entity, it depends on the UploadService and IncidentService classes.
        $uploadsArray = $this->uploadService->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->incidentService->groupIncidents($incidents);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'productLine'               => $productLine,
            'category'                  => $category,
            'categoryButtons'           => $categoryButtons,
            'productLineCategories'     => $productLineCategories,
        ]);
    }

    // This function is used to create a new button to which is attached the uploads.
    #[Route('/create_button/{categoryId}', name: 'admin_create_button')]
    public function createButton(Request $request, ?int $categoryId = null)
    {
        // Check if button name does not contain the disallowed characters
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {

            // Handle the case when button name contains disallowed characters
            $this->addFlash('danger', 'Nom de bouton invalide');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        } else {

            $category = $this->categoryRepository->find($categoryId);
            // Look if the the button already exists
            $buttonname = $request->request->get('buttonname') . '.' . $category->getName();
            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);

            // If the button already exists, redirect to the category manager page and display a flash message.
            if ($button) {
                $this->addFlash('danger', 'Le bouton existe déjà');
                return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
                // If the button does not exist, create it and redirect to the category manager page and display a flash message. It depends on the FolderService class.
            } else {
                $count = $this->buttonRepository->count(['category' => $categoryId]);
                $sortOrder = $count + 1;
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($category);
                $button->setSortOrder($sortOrder);
                $button->setCreator($this->getUser());
                $this->em->persist($button);
                $this->em->flush();
                $this->folderService->folderStructure($buttonname);

                $this->addFlash('success', 'Le bouton a été créé');
                return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
            }
        }
    }

    #[Route('/delete_button/{buttonId}', name: 'admin_delete_button')]

    // This function is used to delete a button and all the uploads attached to it.
    public function deleteEntityButton(int $buttonId): Response
    {
        $entityType = 'button';

        $button = $this->buttonRepository->find($buttonId);
        $categoryId = $button->getCategory()->getid();

        // Check if the user is the creator of the button or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {
            // This function is used to delete a button and all the uploads attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $buttonId);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        }

        if ($response == true) {
            $this->addFlash('success', 'Le bouton ' . $entityType . ' a été supprimé');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        } else {
            $this->addFlash('danger', 'Le bouton ' . $entityType . ' n\'existe pas.');
            return $this->redirectToRoute('app_category_admin', ['categoryId' => $categoryId]);
        }
    }
}
