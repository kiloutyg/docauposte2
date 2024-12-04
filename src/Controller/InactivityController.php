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
    #[Route(path: '/inactivity_check', name: 'inactivity_check')]
    public function inactivityCheck(Request $request)
    {
        if (!$this->settingsService->getSettings()->isIncidentAutoDisplay()) {
            $this->logger->info('isIncidentAutoDisplay false');
            return new JsonResponse(false);
        }

        if ($this->security->getUser()) {
            $this->logger->info('getUser true');
            return new JsonResponse(false);
        }

        if ($request->isXmlHttpRequest()) {
            $this->logger->info('isXmlHttpRequest true');
            return new JsonResponse(false);
        }

        $this->logger->info('inactivityCheck in controller', [$request]);

        $response = $this->incidentRedirectService->inactivityCheck($request);
        
        return $response;
    }
}
