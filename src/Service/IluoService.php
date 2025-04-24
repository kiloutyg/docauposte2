<?php

namespace App\Service;

use App\Service\ProductsService;
use App\Service\QualityRepService;
use App\Service\ShiftLeadersService;
use App\Service\WorkstationService;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IluoService extends AbstractController
{
    private $logger;
    private $productsService;
    private $qualityRepService;
    private $shiftLeadersService;
    private $workstationService;
    public function __construct(
        LoggerInterface                     $logger,

        ProductsService                     $productsService,
        QualityRepService                   $qualityRepService,
        ShiftLeadersService                 $shiftLeadersService,
        WorkstationService                  $workstationService
    ) {
        $this->logger                       = $logger;

        $this->productsService              = $productsService;
        $this->qualityRepService            = $qualityRepService;
        $this->shiftLeadersService          = $shiftLeadersService;
        $this->workstationService           = $workstationService;
    }


    
    // Transversal elements



    public function iluoComponentFormManagement(string $entityType, Form $form, Request $request): Response
    {
        $this->logger->info('iluoComponentFormManagement', [$entityType, $form, $request]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Convert entityType to service property name (e.g., 'shiftLeaders' -> 'shiftLeadersService')
                $serviceProperty = lcfirst($entityType) . 'Service';
                // Check if service property exists in the current class
                if (!property_exists($this, $serviceProperty)) {
                    throw new \InvalidArgumentException("Service not found for entity type: $entityType");
                }
                $service = $this->$serviceProperty;
                // Call the appropriate method
                $methodName = lcfirst($entityType) . 'CreationFormProcessing';
                if (!method_exists($service, $methodName)) {
                    throw new \InvalidArgumentException("Method $methodName not found in service");
                }
                $entityName = $service->$methodName($form);
                $this->addFlash('success', "L'entité $entityName a bien été ajoutée.");
            } catch (\Exception $e) {
                $this->logger->error('Issue in form submission', [$e->getMessage()]);
                $this->addFlash('error', 'Issue in form submission ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            $this->logger->error('Invalid form', [$form->getErrors()]);
            $this->addFlash('error', 'Invalid form ' . $form->getErrors());
        }
        return $this->redirectToRoute($this->routeNameDetermination($entityType));
    }




    public function routeNameDetermination(string $entityType): string
    {
        if (in_array($entityType, ['products', 'shiftLeaders', 'qualityRep'])) {
            $route = 'app_iluo_' . strtolower($entityType) . '_general_elements_admin';
        } elseif ($entityType === 'workstation') {
            $route = 'app_iluo_creation_workstation_admin';
        } elseif ($entityType === 'checklist') {
            $route = 'app_iluo_checklist_admin';
        } else {
            $this->logger->error('Invalid entity type', [$entityType]);
            throw new \InvalidArgumentException("Invalid entity type: $entityType");
        }
        $this->logger->info('Redirecting to route', [$route]);
        return $route;
    }
}
