<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Category;
use App\Entity\ProductLine;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;

#[Route('/productline_admin', name: 'app_productLine_')]
// This controller manage the logic of the productLine admin interface
class ProductLineAdminController extends AbstractController
{
    private $entityManagerFacade;
    private $contentManagerFacade;
    private $authChecker;
    private $errorService;

    public function __construct(
        EntityManagerFacade $entityManagerFacade,
        ContentManagerFacade $contentManagerFacade,
        AuthorizationCheckerInterface $authChecker,
        ErrorService $errorService
    ) {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->contentManagerFacade = $contentManagerFacade;
        $this->authChecker = $authChecker;
        $this->errorService = $errorService;
    }


    #[Route('/{productLineId}', name: 'admin')]
    // This function is responsible for rendering the productLine's admin interface
    public function productLineAdmin(?int $productLineId = null, ?ProductLine $productLine = null): Response
    {
        $pageLevel = 'productLine';

        if ($productLine === null) {
            $productLine = $this->entityManagerFacade->find('productLine', $productLineId);
        }
        if (!$productLine) {
            return $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $zone = $productLine->getZone();
        $zoneProductLines = $zone->getProductLines();

        // Get all the uploads and incidents related to the productLine
        $uploads = $this->entityManagerFacade->uploadsByParentEntity(
            'productLine',
            $productLine
        );
        $incidents = $this->entityManagerFacade->incidentsByParentEntity(
            'productLine',
            $productLine
        );

        // Group the uploads and incidents by parents entity
        $uploadsArray = $this->contentManagerFacade->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->contentManagerFacade->groupIncidents($incidents);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'productLine'               => $productLine,
            'incidentCategories'        => $this->entityManagerFacade->getIncidentCategories(),
            'zoneProductLines'          => $zoneProductLines
        ]);
    }



    // This function will create a new category
    #[Route('/create_category/{productLineId}', name: 'admin_create_category')]
    public function createCategory(Request $request, ?int $productLineId = null)
    {
        $productLine = $this->entityManagerFacade->find('productLine', $productLineId);

        if (!preg_match("/^[^.]+$/", $request->request->get('categoryname'))) {
            // Handle the case when category name contains disallowed characters
            $this->addFlash('danger', 'Nom de catégorie invalide');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {

            // Check if the category already exists by looking for a category with the same name
            $categoryname = $request->request->get('categoryname') . '.' . $productLine->getName();
            $category = $this->entityManagerFacade->findOneBy('category', ['name' => $categoryname]);

            // If the category already exists, redirect to the productLine admin interface  with a flash message
            if ($category) {
                $this->addFlash('danger', 'La catégorie existe deja');
                return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
                // If the category doesn't exist, create it and redirect to the productLine admin interface with a flash message
            } else {
                $count = $this->entityManagerFacade->count('category', ['productLine' => $productLineId]);
                $sortOrder = $count + 1;
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $category->setSortOrder($sortOrder);
                $category->setCreator($this->getUser());

                $em = $this->entityManagerFacade->getEntityManager();
                $em->persist($category);
                $em->flush();
                $this->contentManagerFacade->folderStructure($categoryname);
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

        $category = $this->entityManagerFacade->find('category', $categoryId);
        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {

            $response = $this->entityManagerFacade->deleteEntity($entityType, $categoryId);
        } else {
            $productLineId = $category->getProductLine()->getId();
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }

        $productLineId = $category->getProductLine()->getId();
        if ($response) {
            $this->addFlash('success', 'La catégorie ' . $category->getName() . ' a été supprimée.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        } else {
            $this->addFlash('danger', 'La catégorie ' . $category->getName() . ' n\'existe pas.');
            return $this->redirectToRoute('app_productLine_admin', ['productLineId' => $productLineId]);
        }
    }
}
