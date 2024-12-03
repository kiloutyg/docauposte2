<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\SecurityBundle\Security;

use App\Repository\SettingsRepository;

use App\Service\SettingsService;
use App\Service\IncidentRedirectService;

class IncidentRedirectSubscriber implements EventSubscriberInterface
{
    private $router;
    private $logger;


    private $security;

    private $settingsRepository;

    private $settingsService;
    private $incidentRedirectService;

    public function __construct(
        RouterInterface $router,
        LoggerInterface             $logger,

        Security $security,
        SettingsRepository $settingsRepository,


        SettingsService $settingsService,
        IncidentRedirectService $incidentRedirectService,
    ) {
        $this->router = $router;
        $this->logger = $logger;


        $this->security = $security;

        $this->settingsRepository = $settingsRepository;

        $this->settingsService = $settingsService;
        $this->incidentRedirectService = $incidentRedirectService;
    }


    public function timerOnIncidentRedirectKernelRequest(RequestEvent $event): void
    {

        if (!$this->settingsService->getSettings()->isIncidentAutoDisplay()) {
            $this->logger->info('isIncidentAutoDisplay false');
            return;
        }

        if ($this->security->getUser()) {
            $this->logger->info('getUser true');
            return;
        }

        if (!$event->isMainRequest()) {
            $this->logger->info('isMainRequest false');
            return;
        }

        $request = $event->getRequest();

        // Get the session from the request
        $session = $request->getSession();

        // Ensure the session is started
        if (!$session->isStarted()) {
            $session->start();
        }

        $currentTime = time();
        $this->logger->info('currentTime', [$currentTime]);

        $lastUsed = $session->getMetadataBag()->getLastUsed();
        $this->logger->info('lastUsed', [$lastUsed]);

        $idleTime = $currentTime - $lastUsed;
        $this->logger->info('idleTime', [$idleTime]);

        $incidentAutoDisplayTimer = $this->settingsRepository->getIncidentAutoDisplayTimerInSeconds();
        $this->logger->info('idleTime>incidentAutoDisplayTimer', [$idleTime > $incidentAutoDisplayTimer]);

        if ($idleTime > $incidentAutoDisplayTimer) {
            $session->invalidate();

            $routeName = $request->attributes->get('_route');
            $this->logger->info('routeName in eventSubscriber', [$routeName]);

            $routeParams =  $request->attributes->get('_route_params');
            $this->logger->info('routeParam in eventSubscriber', [$routeParams]);


            if (($routeName == 'app_zone' || $routeName == 'app_productLine' || $routeName == 'app_category' || $routeName == 'app_button') && $routeParams != null) {
                $response = $this->incidentRedirectService->incidentRouteDetermination($request);
                if ($response) {
                    $event->setResponse(new RedirectResponse($this->router->generate(
                        'app_mandatory_incident',
                        [
                            'productLineId' => $response[0],
                            'incidentId' => $response[1]
                        ]
                    )));
                }
            }
        }
        return;
    }



    // This method returns an array of events and their associated handlers for this class
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['timerOnIncidentRedirectKernelRequest', 0],
            ],
        ];
    }
}
