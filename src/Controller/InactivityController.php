<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\SecurityBundle\Security;

use App\Service\SettingsService;
use App\Service\IncidentRedirectService;
use App\Service\IncidentService;


#[Route('/', name: 'app_')]
class InactivityController extends AbstractController
{
    private $logger;


    private $security;

    private $incidentService;
    private $settingsService;
    private $incidentRedirectService;

    public function __construct(
        LoggerInterface                 $logger,

        Security                        $security,

        IncidentService                 $incidentService,
        SettingsService                 $settingsService,
        IncidentRedirectService         $incidentRedirectService,
    ) {
        $this->logger                   = $logger;

        $this->security                 = $security;

        $this->incidentService          = $incidentService;
        $this->settingsService          = $settingsService;
        $this->incidentRedirectService  = $incidentRedirectService;
    }


    // Route to check inactivity and respond to the client side 
    #[Route(path: '/inactivity_check', name: 'inactivity_check')]
    public function inactivityCheck(Request $request)
    {
        if (!$this->settingsService->getSettings()->isIncidentAutoDisplay()) {
            $this->logger->info('isIncidentAutoDisplay false');
            return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller isIncidentAutoDisplay false']);
        }

        if ($this->security->getUser()) {
            $this->logger->info('getUser true');
            return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller getUser true']);
        }

        if ($request->isXmlHttpRequest()) {
            $this->logger->info('isXmlHttpRequest true');
            return new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller isXmlHttpRequest true']);
        }

        $this->logger->info('inactivityCheck in controller', [$request]);

        $response = $this->incidentRedirectService->inactivityCheck($request);

        return $response;
    }





    // Render the redirected incidents page
    #[Route('/productline/{productLineId}/redirected_incident/{incidentId}', name: 'redirected_incident')]
    public function redirectedIncident(int $productLineId = null, int $incidentId = null)
    {
        $this->logger->info('redirect incident is being called', ['productLineId' => $productLineId, 'incidentId' => $incidentId]);

        $response = $this->incidentService->displayIncident($productLineId, $incidentId);
        $incident       = $response[0];
        $productLine    = $response[1];

        return $this->render(
            '/services/incidents/redirected_incidents_view.html.twig',
            [
                'incidentId'        => $incident->getId(),
                'incidentCategory'  => $incident->getIncidentCategory(),
                'zoneId'            => $productLine->getZone()->getId(),
            ]
        );
    }





    // Render the redirected incidents page
    #[Route('/cycling_incident', name: 'cycling_incident')]
    public function cyclingIncident(Request $request)
    {
        $this->logger->info('cyclingIncident is being called');

        $response = $this->incidentRedirectService->cyclingIncident($request);

        return $response;
    }
}
