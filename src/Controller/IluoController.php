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
    /**
     * Constructor for the IluoController class.
     *
     * Initializes the controller with necessary services for logging, authorization,
     * entity fetching, and ILUO-specific operations.
     *
     * @param LoggerInterface $logger An instance of LoggerInterface for logging purposes.
     * @param AuthorizationCheckerInterface $authChecker An instance of AuthorizationCheckerInterface for checking user permissions.
     * @param EntityFetchingService $entityFetchingService A service for fetching various entities.
     * @param IluoService $iluoService A service specific to ILUO operations.
     */
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



    /**
     * Handles the GET request for the base admin page.
     *
     * This function checks if the incoming request is a GET method and if the user has the ROLE_LINE_ADMIN role.
     * If both conditions are met, it renders the admin template. Otherwise, it adds a warning flash message
     * and redirects to the base application route.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for authorized GET requests
     *                  or a redirect response with a warning flash message for unauthorized or non-GET requests
     */
    #[Route('admin', name: 'admin')]
    public function baseAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET') && $this->authChecker->isGranted('ROLE_LINE_ADMIN')) {
            return $this->render('/services/iluo/iluo_admin.html.twig');
        }
        $this->addFlash('warning', 'Accés non authorisé');
        return $this->redirectToRoute('app_base');
    }





    /**
     * Handles the GET request for the checklist admin page.
     *
     * This function checks if the incoming request is a GET method and, if so,
     * renders the checklist admin template. Otherwise, it redirects to the base application route.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for GET requests
     *                  or a redirect response for non-GET requests
     */
    #[Route('admin/checklist', name: 'checklist_admin')]
    public function checklistAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_checklist_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }





    /**
     * Handles the GET request for the general elements admin page.
     *
     * This function checks if the incoming request is a GET method and, if so,
     * renders the general elements admin template. Otherwise, it redirects to the base application route.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for GET requests
     *                  or a redirect response for non-GET requests
     */
    #[Route('admin/general_elements', name: 'general_elements_admin')]
    public function generalElementsAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_general_elements_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }



    /**
     * Handles the GET and POST requests for the Products general elements admin page.
     *
     * This function retrieves all products, creates a form for a new product,
     * processes form submissions, and renders the products admin template.
     *
     * @param Request $request The current HTTP request object containing all request data.
     *
     * @return Response A Symfony Response object containing either the rendered template
     *                  or a redirect response after form processing.
     */
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





    /**
     * Handles the GET and POST requests for the Shift Leaders general elements admin page.
     *
     * This function retrieves all shift leaders, creates a form for a new shift leader,
     * processes form submissions, and renders the shift leaders admin template.
     *
     * @param Request $request The current HTTP request object containing all request data.
     *
     * @return Response A Symfony Response object containing either the rendered template
     *                  or a redirect response after form processing.
     */
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


    /**
     * Handles the GET and POST requests for the Quality Representative general elements admin page.
     *
     * This function retrieves all quality representatives, creates a form for a new quality representative,
     * processes form submissions, and renders the quality representative admin template.
     *
     * @param Request $request The current HTTP request object containing all request data.
     *
     * @return Response A Symfony Response object containing either the rendered template
     *                  or a redirect response after form processing.
     */
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




    /**
     * Handles the GET request for the workstation admin page.
     *
     * This function checks if the incoming request is a GET method and, if so,
     * renders the workstation admin template. Otherwise, it redirects to the base application route.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for GET requests
     *                  or a redirect response for non-GET requests
     */
    #[Route('admin/workstation', name: 'workstation_admin')]
    public function workstationAdminPageGet(Request $request): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('/services/iluo/iluo_admin_component/iluo_workstation_admin.html.twig');
        }
        return $this->redirectToRoute('app_base');
    }




    /**
     * Handles the creation and management of workstations in the admin interface.
     *
     * This function processes both GET and POST requests for the workstation creation page.
     * It creates a new workstation form, handles form submissions (including AJAX requests
     * for zone changes), and renders the workstation creation template.
     *
     * @param Request $request The current HTTP request object containing all request data.
     *
     * @return Response A Symfony Response object containing the rendered template or
     *                  a redirect response after form processing.
     */
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
