<?php

namespace App\Controller;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Products;
use App\Entity\ShiftLeaders;
use App\Entity\QualityRep;
use App\Entity\Workstation;

use App\Form\ProductType;
use App\Form\ShiftLeadersType;
use App\Form\QualityRepType;
use App\Form\WorkstationType;

use App\Service\EntityFetchingService;
use App\Service\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoController extends AbstractController
{
    private $logger;
    private $authChecker;
    private $entityFetchingService;
    private $iluoService;
    public function __construct(
        LoggerInterface                     $logger,
        AuthorizationCheckerInterface       $authChecker,

        EntityFetchingService               $entityFetchingService,
        IluoService                         $iluoService
    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->entityFetchingService        = $entityFetchingService;
        $this->iluoService                  = $iluoService;
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

    // Checklist

    #[Route('admin/checklist', name: 'checklist_admin')]
    public function checklistAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_checklist_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }


    // General elements


    #[Route('admin/general_elements', name: 'general_elements_admin')]
    public function generalElementsAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin.html.twig');
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
            $this->logger->debug('products form submitted', [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement('products', $productForm, $request);
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
            $this->logger->debug('shiftLeaders form submitted', [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement('shiftLeaders', $shiftLeadersForm, $request);
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin_component/iluo_shiftleaders_general_elements_admin.html.twig', [
            'shiftLeadersForm' => $shiftLeadersForm->createView(),
            'shiftLeaders'    => $shiftLeaders,
        ]);
    }


    #[Route('admin/qualityrep_general_elements', name: 'qualityrep_general_elements_admin')]
    public function qualityRepGeneralElementsAdminPageGet(Request $request): Response
    {
        $qualityRep = $this->entityFetchingService->getQualityRep();
        $newQualityRep = new QualityRep;
        $qualityRepForm = $this->createForm(QualityRepType::class, $newQualityRep);
        if ($request->isMethod('POST')) {
            $this->logger->debug('qualityRep form submitted', [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement('qualityRep', $qualityRepForm, $request);
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin_component/iluo_qualityrep_general_elements_admin.html.twig', [
            'qualityRepForm' => $qualityRepForm->createView(),
            'qualityRep'    => $qualityRep,
        ]);
    }



    // Workstation

    #[Route('admin/workstation', name: 'workstation_admin')]
    public function workstationAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_workstation_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }


    #[Route('admin/creation_workstation', name: 'creation_workstation_admin')]
    public function creationWorkstationAdminPageGet(Request $request): Response
    {
        $this->logger->info('GET request on creation_workstation_admin full request : ', [$request->request->all()]);
        $newWorkstation = new Workstation();
        $workstationForm = $this->createForm(WorkstationType::class, $newWorkstation);
        if ($request->isMethod('POST')) {
            $this->logger->info('workstation form submitted method POST ', [$request->request->all()]);
            // Check if this is an AJAX request for zone change
            if ($request->request->has('ajax_change')) {

                $this->logger->info('workstation form submitted with AJAX zone change', [$request->request->all()]);

                // Get the form data from the request
                $formData = $request->request->has('workstation') ? $request->request->all('workstation') : [];
                // Handle the form data
                $workstationForm->submit($formData, false);
            } else {
                $this->logger->info('workstation form submitted', [$request->request->all()]);
                return $this->iluoService->iluoComponentFormManagement('workstation', $workstationForm, $request);
            }
        }
        return $this->render('/services/iluo/iluo_admin_component/iluo_workstation_admin_component/iluo_creation_workstation_admin.html.twig', [
            'workstationForm' => $workstationForm->createView(),
            'workstations' => $this->entityFetchingService->getWorkstations(),
        ]);
    }
}
