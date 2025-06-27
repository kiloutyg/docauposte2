<?php

namespace App\Controller\Iluo;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Products;
use App\Entity\ShiftLeaders;
use App\Entity\QualityRep;

use App\Form\Iluo\ProductType;
use App\Form\Iluo\ShiftLeadersType;
use App\Form\Iluo\QualityRepType;

use App\Service\EntityFetchingService;
use App\Service\Iluo\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoGeneralElementsController extends AbstractController
{
    private $logger;
    private $entityFetchingService;
    private $iluoService;

    /**
     * Constructor for the IluoController class.
     *
     * Initializes the controller with necessary services for logging, authorization,
     * entity fetching, and ILUO-specific operations.
     *
     * @param LoggerInterface $logger An instance of LoggerInterface for logging purposes.
     * @param EntityFetchingService $entityFetchingService A service for fetching various entities.
     * @param IluoService $iluoService A service specific to ILUO operations.
     */
    public function __construct(
        LoggerInterface                     $logger,

        EntityFetchingService               $entityFetchingService,
        IluoService                         $iluoService
    ) {
        $this->logger                       = $logger;

        $this->entityFetchingService        = $entityFetchingService;
        $this->iluoService                  = $iluoService;
    }




    /**
     * Provides the content for the general elements administration section.
     *
     * This function renders a template that contains the main content for the general elements
     * administration interface. It serves as the primary content area for managing general elements
     * such as products, shift leaders, and quality representatives.
     *
     * @return Response A Symfony Response object containing the rendered template
     *                  for the general elements administration content.
     */
    #[Route('admin/general_elements/content', name: 'general_elements_admin_content')]
    public function generalElementsContentGet(): Response
    {
        return $this->render('services/iluo/iluo_admin_component/iluo_general_elements_admin_content.html.twig', []);
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
}
