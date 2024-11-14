<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;

// This controller manage the logic of the productline admin interface
class ProductLineAdminController extends FrontController
{
    #[Route('/productline_admin/{productlineId}', name: 'app_productline_admin')]

    // This function is responsible for rendering the productline's admin interface
    public function index(int $productlineId = null): Response
    {
        $pageLevel = 'productLine';

        $productLine = $this->productLineRepository->find($productlineId);
        $zone = $productLine->getZone();

        // Get all the uploads and incidents related to the productline
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'productLine',
            $productLine->getId()
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'productLine',
            $productLine->getId()
        );

        // Group the uploads and incidents by parents entity
        $groupedUploads = $this->uploadService->groupUploads($uploads);
        $groupIncidents = $this->incidentService->groupIncidents($incidents);
        $groupedValidatedUploads = $this->uploadService->groupValidatedUploads($uploads);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'productLine'               => $productLine
        ]);
    }

    // This function will create a new User 
    #[Route('/productline_admin/create_manager/{productlineId}', name: 'app_productline_admin_create_manager')]
    public function createManager(int $productlineId = null, Request $request): Response
    {
        $productLine = $this->productLineRepository->find($productlineId);
        $zone = $productLine->getZone();

        $error = null;
        $result = $this->accountService->createAccount(
            $request,
            $error
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_productline', [
            'productlineId' => $productlineId,
        ]);
    }

    // This function will create a new category
    #[Route('/productline_admin/create_category/{productlineId}', name: 'app_productline_admin_create_category')]
    public function createCategory(Request $request, int $productlineId = null)
    {
        $productLine = $this->productLineRepository->find($productlineId);
        $zone = $productLine->getZone();

        if (!preg_match("/^[^.]+$/", $request->request->get('categoryname'))) {
            // Handle the case when category name contains disallowed characters
            $this->addFlash('danger', 'Nom de catégorie invalide');
            return $this->redirectToRoute('app_productline_admin', [
                'productlineId'       => $productlineId(),
            ]);
        } else {

            // Check if the category already exists by looking for a category with the same name
            $categoryname = $request->request->get('categoryname') . '.' . $productLine->getName();
            $category = $this->categoryRepository->findOneBy(['name' => $categoryname]);

            // If the category already exists, redirect to the productline admin interface  with a flash message
            if ($category) {
                $this->addFlash('danger', 'La catégorie existe deja');
                return $this->redirectToRoute('app_productline_admin', [
                    'productlineId'       => $productlineId(),

                ]);
                // If the category doesn't exist, create it and redirect to the productline admin interface with a flash message
            } else {
                $count = $this->categoryRepository->count(['ProductLine' => $productLine->getId()]);
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
                return $this->redirectToRoute('app_productline_admin', [
                    'productlineId'     => $productlineId,
                ]);
            }
        }
    }

    #[Route('/productline_admin/delete_category/{categoryId}', name: 'app_productline_admin_delete_category')]
    // This function will delete a category and all of its children entities, it depends on the entitydeletionService
    public function deleteEntityCategory(int $categoryId): Response
    {
        $entityType = 'category';
        $entity = $this->categoryRepository->find($categoryId);
        if (empty($entity)){
            return $this->redirectToRoute('app_base');
        };
        $productLineId = $entity->getProductLine()->getId();

        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN") || $this->getUser() === $entity->getCreator()) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $entity->getId());
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_productline_admin', [
                'productlineId'   => $productLineId,
            ]);
        }

        if ($response == true) {

            $this->addFlash('success', 'La catégorie ' . $entityType . ' a été supprimée');
            return $this->redirectToRoute('app_productline_admin', [
                'productlineId'   => $productLineId,
            ]);
        } else {
            $this->addFlash('danger', 'La catégorie ' . $entityType . ' n\'existe pas');
            return $this->redirectToRoute('app_productline_admin', [
                'productlineId'   => $productLineId,
            ]);
        }
    }
}
