<?php

namespace App\Controller\Iluo;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Workstation;

use App\Form\Iluo\WorkstationType;

use App\Service\EntityFetchingService;
use App\Service\Iluo\IluoService;


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
     * Retrieves the content for the workstation admin section.
     *
     * This function renders the template that contains the main content
     * for the workstation administration interface.
     *
     * @return Response A Symfony Response object containing the rendered template
     *                  for the workstation admin content.
     */
    #[Route(path: 'admin/workstation/content', name: 'workstation_admin_content')]
    public function workstationChecklistContentGet(): Response
    {
        return $this->render('/services/iluo/iluo_admin_component/iluo_workstation_admin_content.html.twig', []);
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
    #[Route('admin/workstation/creation', name: 'creation_workstation_admin')]
    public function workstationCreationAdminPageGet(Request $request): Response
    {
        $this->logger->info(message: 'GET request on creation_workstation_admin full request : ', context: [$request->request->all()]);
        $newWorkstation = new Workstation();
        $workstationForm = $this->createForm(type: WorkstationType::class, data: $newWorkstation);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->info(message: 'workstation form submitted method POST ', context: [$request->request->all()]);
            // Check if this is an AJAX request for zone change
            if ($request->request->has(key: 'ajax_change')) {

                $this->logger->info(message: 'workstation form submitted with AJAX zone change', context: [$request->request->all()]);

                // Get the form data from the request
                $formData = $request->request->has(key: 'workstation') ? $request->request->all(key: 'workstation') : [];
                // Handle the form data
                $workstationForm->submit(submittedData: $formData, clearMissing: false);
            } else {
                $this->logger->info(message: 'workstation form submitted', context: [$request->request->all()]);
                return $this->iluoService->iluoComponentFormManagement(entityType: 'workstation', form: $workstationForm, request: $request);
            }
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_workstation_admin_component/iluo_creation_workstation_admin.html.twig', parameters: [
            'workstationForm' => $workstationForm->createView(),
            'workstations' => $this->entityFetchingService->getWorkstations(),
        ]);
    }
}
