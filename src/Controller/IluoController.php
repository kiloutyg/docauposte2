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
use App\Entity\ShiftLeaders;

use App\Form\ProductType;
use App\Form\ShiftLeadersType;

use App\Service\EntityFetchingService;
use App\Service\EntityDeletionService;
use App\Service\ProductsService;
use App\Service\ShiftLeadersService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoController extends AbstractController
{
    private $logger;
    private $authChecker;
    private $entityFetchingService;
    private $entityDeletionService;
    private $productsService;
    private $shiftLeadersService;
    public function __construct(
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        EntityFetchingService           $entityFetchingService,
        EntityDeletionService           $entityDeletionService,
        ProductsService                 $productsService,
        ShiftLeadersService             $shiftLeadersService
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->entityFetchingService        = $entityFetchingService;
        $this->entityDeletionService        = $entityDeletionService;
        $this->productsService              = $productsService;
        $this->shiftLeadersService          = $shiftLeadersService;
    }



    #[Route('admin', name: 'admin')]
    public function baseAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET') && $this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->render('/services/iluo/iluo_admin.html.twig');
        }
        $this->addFlash('warning', 'Accés non authorisé');
        return $this->redirectToRoute('app_base');
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


    #[Route('admin/products_general_elements', name: 'products_general_elements_admin')]
    public function productsGeneralElementsAdminPageGet(Request $request): Response
    {
        $products = $this->entityFetchingService->getProducts();
        $newProduct = new Products;
        $productForm = $this->createForm(ProductType::class, $newProduct);
        if ($request->isMethod('POST')) {
            $this->logger->info('products form submitted', [$request->request->all()]);
            return $this->generalElementsFormManagement('products', $productForm, $request);
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin_component/iluo_products_general_elements_admin.html.twig', [
            'productForm' => $productForm->createView(),
            'products'    => $products,
        ]);
    }


    #[Route('admin/shiftleaders_general_elements', name: 'shiftleaders_general_elements_admin')]
    public function shiftLeadersGeneralElementsAdminPageGet(Request $request): Response
    {
        $shiftLeaders = $this->entityFetchingService->getShiftLeaders();
        $newShiftLeaders = new ShiftLeaders;
        $shiftLeadersForm = $this->createForm(ShiftLeadersType::class, $newShiftLeaders);
        if ($request->isMethod('POST')) {
            $this->logger->info('shiftLeaders form submitted', [$request->request->all()]);
            return $this->generalElementsFormManagement('shiftLeaders', $shiftLeadersForm, $request);
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin_component/iluo_shiftleaders_general_elements_admin.html.twig', [
            'shiftLeadersForm' => $shiftLeadersForm->createView(),
            'shiftLeaders'    => $shiftLeaders,
        ]);
    }


    public function generalElementsFormManagement(string $entityType, Form $form, Request $request): Response
    {
        $this->logger->info('generalElementsFormManagement', [$entityType, $form, $request]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Convert entityType to service property name (e.g., 'shiftLeaders' -> 'shiftLeadersService')
                $serviceProperty = lcfirst($entityType) . 'Service';
                if (!property_exists($this, $serviceProperty)) {
                    throw new \InvalidArgumentException("Service not found for entity type: $entityType");
                }
                $service = $this->$serviceProperty;

                // Call the appropriate method
                $methodName = lcfirst($entityType) . 'CreationFormProcessing';
                if (!method_exists($service, $methodName)) {
                    throw new \InvalidArgumentException("Method $methodName not found in service");
                }

                $entityName = $service->$methodName($form);
                $this->addFlash('success', "L'entité $entityName a bien été ajoutée.");
            } catch (\Exception $e) {
                $this->logger->error('Issue in form submission', [$e->getMessage()]);
                $this->addFlash('error', 'Issue in form submission ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->logger->error('Invalid form', [$form->getErrors()]);
            $this->addFlash('error', 'Invalid form ' . $form->getErrors());
        }

        return $this->redirectToRoute($this->routeNameDetermination($entityType));
    }


    #[Route('admin/delete_entity/{entityType}/{entityId}', name: 'delete_entity')]
    public function deleteIluoEntity(string $entityType, int $entityId)
    {
        $this->logger->debug('Deleting entity of type: ' . $entityType . 'with id: ' . $entityId);
        try {
            $this->entityDeletionService->deleteEntity($entityType, $entityId);
            $this->addFlash('success', 'Le ' . $entityType . ' a bien été supprimé.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Issue while trying to delete the entity' . $e->getMessage());
            $this->logger->error('Error while deleting entity', [$e->getMessage()]);
        } finally {
            return $this->redirectToRoute($this->routeNameDetermination($entityType));
        }
    }


    public function routeNameDetermination(string $entityType): string
    {
        $route = 'app_iluo_' . strtolower($entityType) . '_general_elements_admin';
        $this->logger->info('Redirecting to route', [$route]);
        return $route;
    }
}
