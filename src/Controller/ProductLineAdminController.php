<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\AccountService;
use App\Service\UploadsService;
use App\Service\IncidentsService;


use App\Entity\Category;

// This controller manage the logic of the productline admin interface
class ProductLineAdminController extends BaseController
{
    #[Route('/productline_admin/{productline}', name: 'app_productline_admin')]

    // This function is responsible for rendering the productline's admin interface
    public function index(IncidentsService $incidentsService, UploadsService $uploadsService, string $productline = null): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $productline]);
        $zone = $productLine->getZone();

        // Get all the uploads and incidents related to the productline
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'productline',
            $productLine->getId()
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'productline',
            $productLine->getId()
        );

        // Group the uploads and incidents by parents entity
        $groupedUploads = $uploadsService->groupUploads($uploads);
        $groupIncidents = $incidentsService->groupIncidents($incidents);



        return $this->render('productline_admin/productline_admin.html.twig', [
            'groupedUploads'    => $groupedUploads,
            'groupincidents'    => $groupIncidents,
            'zone'              => $zone,
            'productLine'       => $productLine,
            'categories'        => $this->categoryRepository->findAll(),
            'uploads'           => $this->uploadRepository->findAll(),
            'users'             => $this->userRepository->findAll(),
            'buttons'           => $this->buttonRepository->findAll(),
            'incidents'         => $this->incidentRepository->findAll(),
            'incidentCategories' => $this->incidentCategoryRepository->findAll(),

        ]);
    }

    // This function will create a new User 
    #[Route('/productline_admin/create_manager/{productline}', name: 'app_productline_admin_create_manager')]
    public function createManager(string $productline = null, AccountService $accountService, Request $request): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $productline]);
        $zone = $productLine->getZone();

        $error = null;
        $result = $accountService->createAccount(
            $request,
            $error,
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_productline', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'uploads'     => $this->uploadRepository->findAll(),
            'productline' => $productLine->getName(),
            'categories'  => $this->categoryRepository->findAll(),
            'productLine' => $productLine,
        ]);
    }

    // This function will create a new category
    #[Route('/productline_admin/create_category/{productline}', name: 'app_productline_admin_create_category')]
    public function createCategory(Request $request, string $productline = null)
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $productline]);
        $zone = $productLine->getZone();

        if (!preg_match("/^[^.]+$/", $request->request->get('categoryname'))) {
            // Handle the case when category name contains disallowed characters
            $this->addFlash('danger', 'Nom de catégorie invalide');
            return $this->redirectToRoute('app_productline_admin', [
                'controller_name'   => 'LineAdminController',
                'zone'          => $zone,
                'name'          => $zone->getName(),
                'productLine'   => $productLine,
                'productline'   => $productLine->getName(),
                'categories'    => $this->categoryRepository->findAll(),
            ]);
        } else {


            // Check if the category already exists by looking for a category with the same name
            $categoryname = $request->request->get('categoryname') . '.' . $productLine->getName();
            $category = $this->categoryRepository->findOneBy(['name' => $categoryname]);

            // If the category already exists, redirect to the productline admin interface  with a flash message
            if ($category) {
                $this->addFlash('danger', 'La catégorie existe deja');
                return $this->redirectToRoute('app_productline_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'productline'   => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),

                ]);
                // If the category doesn't exist, create it and redirect to the productline admin interface with a flash message
            } else {
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $this->em->persist($category);
                $this->em->flush();
                $this->folderCreationService->folderStructure($categoryname);
                $this->addFlash('success', 'La catégorie a été créée');
                return $this->redirectToRoute('app_productline_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'productline'   => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),
                ]);
            }
        }
    }

    #[Route('/productline_admin/delete_category/{category}', name: 'app_productline_admin_delete_category')]
    // This function will delete a category and all of its children entities, it depends on the entitydeletionService
    public function deleteEntity(string $category): Response
    {
        $entityType = 'category';
        $entityid = $this->categoryRepository->findOneBy(['name' => $category]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $productLine = $entityid->getProductLine()->getName();

        if ($entity == true) {

            $this->addFlash('success', 'La catégorie ' . $entityType . ' a été supprimée');
            return $this->redirectToRoute('app_productline_admin', [
                'productline'   => $productLine,
            ]);
        } else {
            $this->addFlash('danger', 'La catégorie ' . $entityType . ' n\'existe pas');
            return $this->redirectToRoute('app_productline_admin', [
                'productline'   => $productLine,
            ]);
        }
    }
}