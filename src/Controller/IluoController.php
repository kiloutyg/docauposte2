<?php

namespace App\Controller;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Products;

use App\Form\ProductType;

use App\Service\EntityFetchingService;
use App\Service\EntityDeletionService;
use App\Service\ProductsService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoController extends AbstractController
{
    private $logger;
    private $authChecker;
    private $entityFetchingService;
    private $entityDeletionService;
    private $productsService;

    public function __construct(
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        EntityFetchingService           $entityFetchingService,
        EntityDeletionService           $entityDeletionService,
        ProductsService                 $productsService
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->entityFetchingService        = $entityFetchingService;
        $this->entityDeletionService        = $entityDeletionService;
        $this->productsService              = $productsService;
    }



    #[Route('admin', name: 'admin')]
    public function baseAdminPageGet(): Response
    {
        if ($this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->render('/services/iluo/iluo_admin.html.twig');
        }
        $this->addFlash('warning', 'Accés non authorisé');
        return $this->redirectToRoute('app_base');
    }

    #[Route('admin/delete_entity/{entityType}/{entityId}', name: 'delete_entity')]
    public function deleteIluoEntity(string $entityType, int $entityId)
    {
        try {
            $this->entityDeletionService->deleteEntity($entityType, $entityId);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Issue while trying to delete the entity' . $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_iluo_admin');
        }
    }


    #[Route('admin/general_elements', name: 'general_elements_admin')]
    public function generalElementsAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }



    #[Route('admin/checklist', name: 'checklist_admin')]
    public function checklistAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_checklist_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }



    #[Route('admin/workstation', name: 'workstation_admin')]
    public function workstationAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_workstation_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }


    #[Route('admin/product_general_elements', name: 'product_general_elements_admin')]
    public function productGeneralElementsAdminPageGet(Request $request): Response
    {
        $products = $this->entityFetchingService->getProducts();
        $newProduct = new Products;
        $productForm = $this->createForm(ProductType::class, $newProduct);
        if ($request->isMethod('POST')) {
            return $this->productGeneralElementsFormManagement($productForm, $request);
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin_component/iluo_product_general_elements_admin.html.twig', [
            'productForm' => $productForm->createView(),
            'products'    => $products,
        ]);
    }

    public function productGeneralElementsFormManagement(Form $productForm, Request $request): Response
    {
        $productForm->handleRequest($request);
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            try {
                $productName = $this->productsService->productCreationFormProcessing($productForm);
                $this->addFlash('success', "Le produit $productName a bien été ajouté.");
            } catch (\Exception $e) {
                $this->addFlash('error', 'Issue in form submission ' . $e->getMessage());
            }
        } elseif ($productForm->isSubmitted()) {
            $this->addFlash('error', 'Invalid form ' . $productForm->getErrors());
        }
        return $this->redirectToRoute('app_iluo_product_general_elements_admin');
    }
}
