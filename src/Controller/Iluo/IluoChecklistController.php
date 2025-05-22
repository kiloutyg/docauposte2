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
     * Provides a Turbo Frame for the checklist admin content section.
     *
     * This function renders a Turbo Frame template that contains the checklist admin content.
     * It is used to load the content section asynchronously within a Turbo Frame context.
     *
     * @return Response A Response object containing the rendered Turbo Frame template for the checklist admin content
     */
    #[Route('admin/checklist/content', name: 'checklist_admin_content')]
    public function checklistAdminContentGet(): Response
    {
        return $this->render('services/iluo/iluo_admin_component/iluo_checklist_admin_content.html.twig', []);
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
    #[Route(path: 'admin/training_material_type_checklist', name: 'trainingmaterialtype_checklist_admin')]
    public function trainingMaterialTypeAdminPageGet(Request $request): Response
    {
        $trainingMaterialTypes = $this->entityFetchingService->findAll(entityType: 'trainingMaterialType');
        $newTrainingMaterialType = new TrainingMaterialType;
        $trainingMaterialTypeForm = $this->createForm(type: TrainingMaterialTypeType::class, data: $newTrainingMaterialType);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'TrainingMaterialType form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'trainingMaterialType', form: $trainingMaterialTypeForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_training_material_type_checklist_admin_component.html.twig', parameters: [
            'trainingMaterialTypeForm' => $trainingMaterialTypeForm->createView(),
            'trainingMaterialTypes'    => $trainingMaterialTypes,
        ]);
    }
}
