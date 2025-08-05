<?php

namespace App\Controller\Iluo;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Entity\IluoLevels;
use App\Entity\Steps;
use App\Entity\StepsSubheadings;
use App\Entity\StepsTitle;
use App\Entity\TrainingMaterialType;

use App\Form\Iluo\IluoLevelsType;
use App\Form\Iluo\StepsType;
use App\Form\Iluo\StepsSubheadingsType;
use App\Form\Iluo\StepsTitleType;
use App\Form\Iluo\TrainingMaterialTypeType;

use App\Service\EntityFetchingService;
use App\Service\Iluo\IluoService;


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
        return $this->render('services/iluo/iluo_admin_component/iluo_checklist_admin_content.html.twig');
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
        $newTrainingMaterialType = new TrainingMaterialType;
        $trainingMaterialTypeForm = $this->createForm(type: TrainingMaterialTypeType::class, data: $newTrainingMaterialType);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'IluoController::trainingMaterialTypeAdminPageGet - TrainingMaterialType POST request', context: [$request->request->all()]);
            $this->logger->debug(message: 'IluoController::trainingMaterialTypeAdminPageGet - TrainingMaterialType Form', context: [$trainingMaterialTypeForm]);

            return $this->iluoService->iluoComponentFormManagement(entityType: 'trainingMaterialType', form: $trainingMaterialTypeForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_training_material_type_checklist_admin_component.html.twig', parameters: [
            'trainingMaterialTypeForm' => $trainingMaterialTypeForm->createView(),
            'trainingMaterialTypes'    => $this->entityFetchingService->findAll(entityType: 'trainingMaterialType'),
        ]);
    }





    /**
     * Handles the GET request for the ILUO levels admin page.
     *
     * This function manages the ILUO levels administration interface by fetching all existing
     * ILUO levels, creating a form for adding new levels, and handling form submissions.
     * For GET requests, it renders the ILUO levels admin template with the form and existing
     * levels data. For POST requests, it logs the form submission and delegates the form
     * processing to the IluoService.
     *
     * @param Request $request The incoming HTTP request object containing request data and method information
     *
     * @return Response Returns either a rendered template response for GET requests containing the ILUO
     *                  levels admin interface, or a redirect response for POST requests after form processing
     */
    #[Route(path: 'admin/iluo_levels', name: 'iluolevels_checklist_admin')]
    public function iluoLevelsAdminPageGet(Request $request): Response
    {
        $newIluoLevel = new IluoLevels;
        $iluoLevelsForm = $this->createForm(type: IluoLevelsType::class, data: $newIluoLevel);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'IluoLevels form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'iluoLevels', form: $iluoLevelsForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_levels_checklist_admin_component.html.twig', parameters: [
            'iluoLevelsForm' => $iluoLevelsForm->createView(),
            'iluoLevels'    => $this->entityFetchingService->findAll(entityType: 'iluoLevels'),

        ]);
    }





    /**
     * Handles the GET request for the steps title admin page.
     *
     * This function manages the steps title administration interface by fetching all existing
     * steps titles, creating a form for adding new steps titles, and handling form submissions.
     * For GET requests, it renders the steps title admin template with the form and existing
     * steps title data. For POST requests, it logs the form submission and delegates the form
     * processing to the IluoService.
     *
     * @param Request $request The incoming HTTP request object containing request data and method information
     *
     * @return Response Returns either a rendered template response for GET requests containing the steps
     *                  title admin interface, or a redirect response for POST requests after form processing
     */
    #[Route(path: 'admin/steps_title_checklist', name: 'stepstitle_checklist_admin')]
    public function stepsTitleAdminPageGet(Request $request): Response
    {
        $newStepTitle = new StepsTitle;
        $stepsTitleForm = $this->createForm(type: StepsTitleType::class, data: $newStepTitle);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'StepsTitle form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'stepsTitle', form: $stepsTitleForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_steps_title_checklist_admin_component.html.twig', parameters: [
            'stepsTitleForm' => $stepsTitleForm->createView(),
            'iluoLevels'    => $this->entityFetchingService->findAll(entityType: 'iluoLevels'),
            'stepsTitle'    => $this->entityFetchingService->findAll(entityType: 'stepsTitle'),
        ]);
    }




    /**
     * Handles the GET request for the steps subheadings admin page.
     *
     * This function manages the steps subheadings administration interface by fetching all existing
     * steps subheadings, creating a form for adding new steps subheadings, and handling form submissions.
     * For GET requests, it renders the steps subheadings admin template with the form and existing
     * steps subheadings data. For POST requests, it logs the form submission and delegates the form
     * processing to the IluoService.
     *
     * @param Request $request The incoming HTTP request object containing request data and method information
     *
     * @return Response Returns either a rendered template response for GET requests containing the steps
     *                  subheadings admin interface, or a redirect response for POST requests after form processing
     */
    #[Route(path: 'admin/steps_subheadings_checklist', name: 'stepssubheadings_checklist_admin')]
    public function stepsSubheadingsAdminPageGet(Request $request): Response
    {
        $newStepSubheadings = new StepsSubheadings;
        $stepsSubheadingsForm = $this->createForm(type: StepsSubheadingsType::class, data: $newStepSubheadings);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'Steps form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'stepsSubheadings', form: $stepsSubheadingsForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_steps_subheadings_checklist_admin_component.html.twig', parameters: [
            'stepsSubheadingsForm' => $stepsSubheadingsForm->createView(),
            'iluoLevels'    => $this->entityFetchingService->findAll(entityType: 'iluoLevels'),
            'stepsSubheadings' => $this->entityFetchingService->findAll(entityType: 'stepsSubheadings'),
        ]);
    }





    /**
     * Handles the GET request for the steps admin page.
     *
     * This function manages the steps administration interface by fetching all existing steps,
     * creating a form for adding new steps, and handling form submissions. For GET requests,
     * it renders the steps admin template with the form and existing steps data. For POST
     * requests, it logs the form submission and delegates the form processing to the IluoService.
     *
     * @param Request $request The incoming HTTP request object containing request data and method information
     *
     * @return Response Returns either a rendered template response for GET requests containing the steps
     *                  admin interface, or a redirect response for POST requests after form processing
     */
    #[Route(path: 'admin/steps_checklist', name: 'steps_checklist_admin')]
    public function stepsAdminPageGet(Request $request): Response
    {
        $newStep = new Steps;
        $stepsForm = $this->createForm(type: StepsType::class, data: $newStep);
        if ($request->isMethod(method: 'POST')) {
            $this->logger->debug(message: 'Steps form submitted', context: [$request->request->all()]);
            return $this->iluoService->iluoComponentFormManagement(entityType: 'steps', form: $stepsForm, request: $request);
        }
        return $this->render(view: '/services/iluo/iluo_admin_component/iluo_checklist_admin_component/iluo_steps_checklist_admin_component.html.twig', parameters: [
            'stepsForm' => $stepsForm->createView(),
            'iluoLevels' => $this->entityFetchingService->findAll(entityType: 'iluoLevels'),
            'steps' => $this->entityFetchingService->findAll(entityType: 'steps'),
        ]);
    }
}
