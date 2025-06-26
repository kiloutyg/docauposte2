<?php

namespace App\Controller\Support;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\SecurityBundle\Security;

use App\Service\SettingsService;
use App\Service\Incident\IncidentRedirectService;
use App\Service\Incident\IncidentService;


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
            $response = new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller isIncidentAutoDisplay false']);
        }

        if ($this->security->getUser()) {
            $response = new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller getUser true']);
        }

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse(['redirect' => false, 'cause' => 'issue in inactivity check controller isXmlHttpRequest true']);
        }

        if (empty($response)) {
            $response = $this->incidentRedirectService->inactivityCheck($request);
        }

        return $response;
    }





    // Render the redirected incidents page
    #[Route('/productLine/{productLineId}/redirected_incident/{incidentId}', name: 'redirected_incident')]
    public function redirectedIncident(?int $productLineId = null, ?int $incidentId = null): Response
    {

        $response = $this->incidentService->displayIncident($productLineId, $incidentId);
        $incident       = $response[0];
        $productLine    = $response[1];

        return $this->render(
            '/services/incident/redirected_incident_view.html.twig',
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
        return $this->incidentRedirectService->cyclingIncident($request);
    }
}
