<?php

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\SecurityBundle\Security;

use App\Service\SettingsService;
use App\Service\IncidentRedirectService;


class InactivityController extends AbstractController
{
    private $logger;


    private $security;


    private $settingsService;
    private $incidentRedirectService;

    public function __construct(
        LoggerInterface                 $logger,

        Security                        $security,

        SettingsService                 $settingsService,
        IncidentRedirectService         $incidentRedirectService,
    ) {
        $this->logger                   = $logger;

        $this->security                 = $security;

        $this->settingsService          = $settingsService;
        $this->incidentRedirectService  = $incidentRedirectService;
    }


    // Route to check inactivity and respond to the client side 
    #[Route(path: '/inactivityCheck', name: 'inactivity_check')]
    public function inactivityCheck(Request $request)
    {

        if (!$this->settingsService->getSettings()->isIncidentAutoDisplay()) {
            $this->logger->info('isIncidentAutoDisplay false');
            return false;
        }

        if ($this->security->getUser()) {
            $this->logger->info('getUser true');
            return false;
        }


        if ($request->isXmlHttpRequest()) {
            return false;
        }

        $response = $this->incidentRedirectService->inactivityCheck($request);
        return $response;
    }
}
