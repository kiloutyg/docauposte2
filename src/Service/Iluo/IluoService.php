<?php

namespace App\Service\Iluo;

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

        ServiceFactory                      $serviceFactory,
    ) {
        $this->logger                       = $logger;

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
}
