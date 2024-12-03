<?php

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\SecurityBundle\Security;

use App\Service\SettingsService;
use App\Service\IncidentRedirectService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class IncidentRedirectController extends AbstractController
{
    private $router;
    private $logger;


    private $security;


    private $settingsService;
    private $incidentRedirectService;

    public function __construct(
        RouterInterface $router,
        LoggerInterface             $logger,

        Security $security,

        SettingsService $settingsService,
        IncidentRedirectService $incidentRedirectService,
    ) {
        $this->router = $router;
        $this->logger = $logger;

        $this->security = $security;

        $this->settingsService = $settingsService;
        $this->incidentRedirectService = $incidentRedirectService;
    }


    // Route to check inactivity and respond to the client side 
    #[Route(path: '/inactivityCheck', name: 'inactivity_check')]
    public function inactivityCheck(Request $request)
    {

        if (!$this->settingsService->getSettings()->isIncidentAutoDisplay()) {
            $this->logger->info('isIncidentAutoDisplay false');
            return;
        }

        if ($this->security->getUser()) {
            $this->logger->info('getUser true');
            return;
        }

        $session = $request->getSession();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        if (!$session->isStarted()) {
            $session->start();
        }

        $currentTime = time();
        $this->logger->info('currentTime', [$currentTime]);

        $lastUsed = $session->getMetadataBag()->getLastUsed();
        $this->logger->info('lastUsed', [$lastUsed]);

        $idleTime = $currentTime - $lastUsed;
        $this->logger->info('idleTime', [$idleTime]);

        $incidentAutoDisplayTimer = $this->settingsService->getIncidentAutoDisplayTimerInSeconds();
        $this->logger->info('idleTime>incidentAutoDisplayTimer', [$idleTime > $incidentAutoDisplayTimer]);

        if ($idleTime > $incidentAutoDisplayTimer) {
            $this->redirectionManagement($session, $request);
        }
        return;
    }


    public function redirectionManagement(SessionInterface $session, Request $request)
    {
        $session->invalidate();

        $routeName = $request->attributes->get('_route');
        $this->logger->info('routeName in eventSubscriber', [$routeName]);

        $routeParams =  $request->attributes->get('_route_params');
        $this->logger->info('routeParam in eventSubscriber', [$routeParams]);


        if (($routeName == 'app_zone' || $routeName == 'app_productLine' || $routeName == 'app_category' || $routeName == 'app_button') && $routeParams != null) {
            $response = $this->incidentRedirectService->incidentRouteDetermination($request);
            if ($response) {
                return $this->redirectToRoute(
                    'app_mandatory_incident',
                    [
                        'productLineId' => $response[0],
                        'incidentId' => $response[1]
                    ]
                );
            }
        }
    }
}
