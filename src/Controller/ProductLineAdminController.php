<?php


namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Category;
use App\Entity\ProductLine;

use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;

use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\FolderCreationService;
use App\Service\IncidentService;
use App\Service\EntityHeritanceService;
use App\Service\SettingsService;
use App\Service\EntityFetchingService;
use App\Service\ErrorService;

#[Route('/productline_admin', name: 'app_productLine_')]
// This controller manage the logic of the productLine admin interface
class ProductLineAdminController extends AbstractController
{

    private $em;
    private $authChecker;

    // Repository methods
    private $categoryRepository;
    private $productLineRepository;


    // Services methods
    private $incidentService;
    private $folderCreationService;
    private $entityHeritanceService;
    private $entitydeletionService;
    private $uploadService;
    private $settingsService;
    private $entityFetchingService;
    private $errorService;

    public function __construct(

        EntityManagerInterface          $em,
        AuthorizationCheckerInterface   $authChecker,

        // Repository methods
        CategoryRepository              $categoryRepository,
        ProductLineRepository           $productLineRepository,

        // Services methods
        IncidentService                 $incidentService,
        EntityHeritanceService          $entityHeritanceService,
        FolderCreationService           $folderCreationService,
        EntityDeletionService           $entitydeletionService,
        UploadService                   $uploadService,
        SettingsService                 $settingsService,
        EntityFetchingService           $entityFetchingService,
        ErrorService                    $errorService,

    ) {
        $this->em                           = $em;
        $this->authChecker                  = $authChecker;

        // Variables related to the repositories
        $this->productLineRepository        = $productLineRepository;
        $this->categoryRepository           = $categoryRepository;

        // Variables related to the services
        $this->incidentService              = $incidentService;
        $this->entityHeritanceService       = $entityHeritanceService;
        $this->folderCreationService        = $folderCreationService;
        $this->uploadService                = $uploadService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->settingsService              = $settingsService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->errorService                 = $errorService;
    }


    #[Route('/{productLineId}', name: 'admin')]
    // This function is responsible for rendering the productLine's admin interface
    public function productLineAdmin(int $productLineId = null, ProductLine $productLine = null): Response
    {
        $pageLevel = 'productLine';

        if ($productLine === null) {
            $productLine = $this->productLineRepository->find($productLineId);
        }
        if (!$productLine) {
            return $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $zone = $productLine->getZone();
        $zoneProductLines = $zone->getProductLines();

        // Get all the uploads and incidents related to the productLine
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'productLine',
            $productLine
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'productLine',
            $productLine
        );

        // Group the uploads and incidents by parents entity
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
            'incidentCategories'        => $this->entityFetchingService->getIncidentCategories(),
            'zoneProductLines'          => $zoneProductLines
        ]);
    }



    // This function will create a new category
    #[Route('/create_category/{productLineId}', name: 'admin_create_category')]
    public function createCategory(Request $request, int $productLineId = null)
    {
        $productLine = $this->productLineRepository->find($productLineId);

        if (!preg_match("/^[^.]+$/", $request->request->get('categoryname'))) {
            // Handle the case when category name contains disallowed characters
            $this->addFlash('danger', 'Nom de catégorie invalide');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {

            // Check if the category already exists by looking for a category with the same name
            $categoryname = $request->request->get('categoryname') . '.' . $productLine->getName();
            $category = $this->categoryRepository->findOneBy(['name' => $categoryname]);

            // If the category already exists, redirect to the productLine admin interface  with a flash message
            if ($category) {
                $this->addFlash('danger', 'La catégorie existe deja');
                return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
                // If the category doesn't exist, create it and redirect to the productLine admin interface with a flash message
            } else {
                $count = $this->categoryRepository->count(['productLine' => $productLineId]);
                $sortOrder = $count + 1;
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $category->setSortOrder($sortOrder);
                $category->setCreator($this->getUser());
                $this->em->persist($category);
                $this->em->flush();
                $this->folderCreationService->folderStructure($categoryname);
                $this->addFlash('success', 'La catégorie a été créée');
                return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
            }
        }
    }

    #[Route('/delete_category/{categoryId}', name: 'admin_delete_category')]
    // This function will delete a category and all of its children entities, it depends on the entitydeletionService
    public function deleteEntityCategory(int $categoryId): Response
    {
        $entityType = 'category';

        $category = $this->categoryRepository->find($categoryId);
        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $categoryId);
        } else {
            $productLineId = $category->getProductLine()->getId();
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }

        $productLineId = $category->getProductLine()->getId();
        if ($response == true) {
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' a été supprimée.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {
            $this->addFlash('danger', 'La catégorie ' . $category->getName() . ' n\'existe pas.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }
    }
}
