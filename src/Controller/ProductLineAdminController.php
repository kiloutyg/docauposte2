<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\AccountService;

use App\Entity\Category;

class ProductLineAdminController extends BaseController
{


    #[Route('/productline_admin/{productline}', name: 'app_productline_admin')]

    public function index(AuthenticationUtils $authenticationUtils, string $productline = null): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $productline]);
        $zone = $productLine->getZone();

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('productline_admin/productline_admin_index.html.twig', [
            'zone'          => $zone,
            'name'          => $zone->getName(),
            'productLine'   => $productLine,
            'id'            => $productLine->getName(),
            'categories'    => $this->categoryRepository->findAll(),
            'error'         => $error,
            'last_username' => $lastUsername,
            'uploads'       => $this->uploadRepository->findAll(),
            'users'         => $this->userRepository->findAll(),
            'buttons'       => $this->buttonRepository->findAll(),

        ]);
    }


    #[Route('/productline_admin/create_manager/{id}', name: 'app_productline_admin_create_manager')]

    public function createManager(string $id = null, AccountService $accountService, Request $request): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productLine->getZone();

        $error = null;
        $result = $accountService->createAccount(
            $request,
            $error,

        );

        if ($result) {
            $this->addFlash('success', 'Account has been created');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_productline', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'uploads'     => $this->uploadRepository->findAll(),
            'id'          => $productLine->getName(),
            'categories'  => $this->categoryRepository->findAll(),
            'productLine' => $productLine,

        ]);
    }


    #[Route('/productline_admin/create_category/{id}', name: 'app_productline_admin_create_category')]
    public function createCategory(Request $request, string $id = null)
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productLine->getZone();

        // Create a category
        if ($request->getMethod() == 'POST') {

            $categoryname = $request->request->get('categoryname');

            $productLine = $this->productLineRepository->findOneBy(['name' => $id]);

            $category = $this->categoryRepository->findOneBy(['name' => $categoryname]);
            if ($category) {
                $this->addFlash('danger', 'Category already exists');
                return $this->redirectToRoute('app_productline_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'id'            => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),

                ]);
            } else {
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $this->em->persist($category);
                $this->em->flush();
                $this->addFlash('success', 'The Category has been created');
                return $this->redirectToRoute('app_productline_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'id'            => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),
                ]);
            }
        }
    }

    #[Route('/productline_admin/delete_category/{id}', name: 'app_productline_admin_delete_category')]
    public function deleteEntity(string $id): Response
    {
        $entityType = 'category';
        $entityid = $this->categoryRepository->findOneBy(['name' => $id]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $productLine = $entityid->getProductLine()->getName();

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_productline_admin', [
                'id'   => $productLine,
            ]);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_productline_admin', [
                'id'   => $productLine,
            ]);
        }
    }
}