<?php

namespace App\Controller\Iluo;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\TrainingMaterialType;

use App\Form\TrainingMaterialTypeType;

use App\Service\EntityFetchingService;
use App\Service\IluoService;


#[Route('/iluo/', name: 'app_iluo_')]
class IluoChecklistController extends AbstractController
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
     * Handles the GET request for the training material type admin page.
     *
     * This function checks if the incoming request is a GET method and, if so,
     * renders the training material type admin template with a form for managing
     * training material types and a list of existing training material types.
     * If the request is a POST method, it logs the submitted form data and
     * delegates the form management to the IluoService.
     *
     * @param Request $request The incoming HTTP request object
     *
     * @return Response Returns either a rendered template response for GET requests
     *                  or a redirect response for non-GET requests
     */
    #[Route(path: 'admin/training_material_type_checklist', name: 'training_material_type_checklist_admin')]
    public function trainingMaterialTypeAdminPageGet(Request $request): Response
    {
        $trainingMaterialTypes = $this->entityFetchingService->findAll(entityType: 'TrainingMaterialType');
        $newTrainingMaterialType = new TrainingMaterialType;
        $trainingMaterialTypeForm = $this->createForm(type: TrainingMaterialTypeType::class, data: $newTrainingMaterialType);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'TrainingMaterialType form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'TrainingMaterialType', form: $trainingMaterialTypeForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_training_material_type_checklist_admin_component.html.twig', parameters: [
            'trainingMaterialTypeForm' => $trainingMaterialTypeForm->createView(),
            'trainingMaterialTypes'    => $trainingMaterialTypes,
        ]);
    }
}
