<?php

namespace App\Service\Iluo;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Factory\ServiceFactory;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IluoService extends AbstractController
{
    private $logger;

    private $entityManagerFacade;
    private $serviceFactory;

    private $iluoLevelsService;
    private $productsService;
    private $qualityRepService;
    private $shiftLeadersService;
    private $stepsService;
    private $stepsSubheadingsService;
    private $stepsTitleService;
    private $workstationService;
    private $trainingMaterialTypeService;
    private $iluoChecklistService;

    /**
     * Constructor for the IluoService class.
     *
     * Initializes the service with required dependencies for logging and
     * managing various ILUO components (products, quality representatives,
     * shift leaders, and workstations).
     *
     * @param LoggerInterface $logger The logger service for recording application events
     * @param ServiceFactory $serviceFactory Factory service for accessing services
     */
    public function __construct(
        LoggerInterface                     $logger,

        EntityManagerFacade                 $entityManagerFacade,
        ServiceFactory                      $serviceFactory,
    ) {
        $this->logger                       = $logger;

        $this->entityManagerFacade          = $entityManagerFacade;
        $this->serviceFactory               = $serviceFactory;

        $this->iluoLevelsService            = $this->serviceFactory->getService(className: 'Iluo\\IluoLevels');
        $this->productsService              = $this->serviceFactory->getService(className: 'Iluo\\Products');
        $this->qualityRepService            = $this->serviceFactory->getService(className: 'Iluo\\QualityRep');
        $this->shiftLeadersService          = $this->serviceFactory->getService(className: 'Iluo\\ShiftLeaders');
        $this->stepsService                 = $this->serviceFactory->getService(className: 'Iluo\\Steps');
        $this->stepsSubheadingsService      = $this->serviceFactory->getService(className: 'Iluo\\StepsSubheadings');
        $this->stepsTitleService            = $this->serviceFactory->getService(className: 'Iluo\\StepsTitle');
        $this->trainingMaterialTypeService  = $this->serviceFactory->getService(className: 'Iluo\\TrainingMaterialType');
        $this->workstationService           = $this->serviceFactory->getService(className: 'Iluo\\Workstation');
        $this->iluoChecklistService         = $this->serviceFactory->getService(className: 'Iluo\\IluoChecklist');
    }




    /**
     * Manages form submission and processing for ILUO component entities.
     *
     * This method handles the form submission process for various entity types,
     * validates the form, processes it through the appropriate service, and
     * redirects to the relevant route with appropriate flash messages.
     *
     * @param string $entityType The type of entity being processed (e.g., 'products', 'shiftLeaders')
     * @param Form $form The form instance containing the submitted data
     * @param Request $request The current HTTP request
     *
     * @return Response A redirect response to the appropriate route after processing
     *
     * @throws \InvalidArgumentException When the service or method for the entity type is not found
     */
    public function iluoComponentFormManagement(string $entityType, Form $form, Request $request): Response
    {
        $this->logger->debug(message: 'iluoService::iluoComponentFormManagement', context: [
            'EntityType' => $entityType,
            'Form' => $form,
            'Request' => $request
        ]);

        $form->handleRequest(request: $request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Convert entityType to service property name (e.g., 'shiftLeaders' -> 'shiftLeadersService')
                $serviceProperty = lcfirst(string: $entityType) . 'Service';
                $this->logger->debug(message: 'iluoService::iluoComponentFormManagement - Service property determined', context: ['servicProperty' => $serviceProperty]);

                // Check if service property exists in the current class
                if (!property_exists(object_or_class: $this, property: $serviceProperty)) {
                    throw new \InvalidArgumentException(message: "Service not found for entity type: $entityType");
                }
                $service = $this->$serviceProperty;

                // Call the appropriate method
                $methodName = lcfirst(string: $entityType) . 'CreationFormProcessing';
                if (!method_exists(object_or_class: $service, method: $methodName)) {
                    throw new \InvalidArgumentException(message: "Method $methodName not found in service");
                }

                $entityName = $service->$methodName($form, $request);
                $this->addFlash(type: 'success', message: "L'entité \" $entityName \" a bien été ajoutée.");
            } catch (\Exception $e) {
                $this->logger->error(message: 'iluoService::iluoComponentFormManagement - Issue in form submission', context: [$e->getMessage()]);
                $this->addFlash(type: 'error', message: 'Issue in form submission ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->logger->error(message: 'iluoService::iluoComponentFormManagement - Invalid form', context: [$form->getErrors()]);
            $this->addFlash(type: 'error', message: 'Invalid form ' . $form->getErrors());
        }

        $this->logger->debug(message: 'iluoService::iluoComponentFormManagement - Redirecting to route', context: ['entityType' => $entityType]);
        return $this->redirectToRoute(route: $this->routeNameDetermination(entityType: $entityType));
    }




    /**
     * Determines the appropriate route name based on the entity type.
     *
     * This method maps different entity types to their corresponding route names
     * in the application's routing system. It handles various ILUO component entities
     * and returns the appropriate route for redirecting after form processing.
     *
     * @param string $entityType The type of entity for which to determine the route
     *                          (e.g., 'products', 'shiftLeaders', 'qualityRep', 'workstation', 'trainingMaterialType')
     *
     * @return string The determined route name for the given entity type
     *
     * @throws \InvalidArgumentException When an unsupported entity type is provided
     */
    public function routeNameDetermination(string $entityType): string
    {
        $this->logger->debug(message: 'iluoService::routeNameDetermination', context: [$entityType]);

        if (in_array(needle: $entityType, haystack: ['products', 'shiftLeaders', 'qualityRep'])) {
            $route = 'app_iluo_' . strtolower(string: $entityType) . '_general_elements_admin';
        } elseif (in_array(needle: $entityType, haystack: ['workstation'])) {
            $route = 'app_iluo_creation_workstation_admin';
        } elseif (in_array(needle: $entityType, haystack: ['trainingMaterialType', 'iluoLevels', 'stepsTitle', 'stepsSubheadings', 'steps'])) {
            $route = 'app_iluo_' . strtolower(string: $entityType) . '_checklist_admin';
        } else {
            $this->logger->error(message: 'iluoService::routeNameDetermination - Invalid entity type', context: [$entityType]);
            throw new \InvalidArgumentException(message: "Invalid entity type: $entityType");
        }

        $this->logger->debug(message: 'iluoService::routeNameDetermination - Redirecting to route', context: [$route]);
        return $route;
    }


    /**
     * Checks for updates in the ILUO checklist.
     *
     * This method delegates the task of checking for ILUO updates to the
     * `iluoChecklistService`. It is used to determine if there have been any
     * changes that require updates to the ILUO records.
     *
     * @return mixed The result of the update check from the `iluoChecklistService`.
     */
    public function checkIluoUpdates()
    {
        $this->logger->debug(message: 'iluoService::iluoCheckUpdate');
        return $this->iluoChecklistService->checkIluoUpdates();
    }


    /**
     * Checks for ILUO updates by a specific operator.
     *
     * This method triggers a check for ILUO updates in the database based on a specific operator.
     * It delegates the task to the `iluoChecklistService` for performing the actual update check.
     *
     * @param int $operatorId The unique identifier of the operator for whom to check updates.
     *
     * @return mixed The result of the update check from the `iluoChecklistService`.
     *               This could be a boolean value indicating success or failure, or an array of updated records.
     */
    public function iluoChecklistUpdatebyOperator(int $operatorId)
    {
        $this->logger->debug(message: 'iluoService::iluoChecklistUpdateByOperator');
        return $this->iluoChecklistService->checkIluoUpdatesBySpecificOperator(operator: $operatorId);
    }

    /**
     * Checks for ILUO updates by a specific upload.
     *
     * This method triggers a check for ILUO updates in the database based on a specific upload ID.
     * It delegates the task to the `iluoChecklistService` for performing the actual update check.
     *
     * @param int $uploadId The unique identifier of the upload for which to check updates.
     *
     * @return mixed The result of the update check from the `iluoChecklistService`.
     *               This could be a boolean value indicating success or failure, or an array of updated records.
     */
    public function iluoChecklistUpdatebySpecificUpload(int $uploadId)
    {
        $this->logger->debug(message: 'iluoService::iluoChecklistUpdateByOperator');
        return $this->iluoChecklistService->checkIluoUpdatesBySpecificUpload(uploadId: $uploadId);
    }


    /**
     * Deletes all ILUO records.
     *
     * This method delegates the deletion of all ILUO records to the
     * `iluoChecklistService`. It is a high-level function that triggers
     * a complete wipe of the ILUO data.
     *
     * @return mixed The result of the deletion operation from the `iluoChecklistService`.
     */
    public function deleteAllIluos()
    {
        $this->logger->debug(message: 'iluoService::deleteAllIluos');
        return $this->iluoChecklistService->deleteAllIluos();
    }



    public function iluoEntitySearchByRequest(Request $request): array
    {
        if ($request->getContentTypeFormat() == 'json') {
            $data = json_decode($request->getContent(), true);
            $name       = $data['search_name'];
            $code       = $data['search_code'];
            $team       = $data['search_team'];
            $uap        = $data['search_uap'];
        } else {
            $name       = $request->request->get('search_name');
            $code       = $request->request->get('search_code');
            $team       = $request->request->get('search_team');
            $uap        = $request->request->get('search_uap');
        }

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $session->set('iluoSearchParams', [
            'searched_name' => $name,
            'searched_code' => $code,
            'searched_team' => $team,
            'searched_uap' => $uap,
        ]);

        return $this->entityManagerFacade->findIluoBySearchQuery(
            $name,
            $code,
            $team,
            $uap
        );
    }
}
