<?php

namespace App\Controller\Iluo;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Workstation;

use App\Form\WorkstationType;

use App\Service\EntityFetchingService;
use App\Service\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoWorkstationController extends AbstractController
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
