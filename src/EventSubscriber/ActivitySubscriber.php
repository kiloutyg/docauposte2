<?php
// src/EventSubscriber/ActivitySubscriber.php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActivitySubscriber implements EventSubscriberInterface
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger   = $logger;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        $this->logger->info('pathinfo', [$request->getPathInfo()]);
        // Skip AJAX inactivity checks
        if ($request->getPathInfo() === '/inactivity_check' || $request->getPathInfo() === '/api/settings'  || $request->getPathInfo() === '/api/user_data'  || $request->getPathInfo() === '/api/entity_data') {
            $this->logger->info('it will return without changing stuff in session becvause of pathinfo');
            return;
        }
        $stuff_route = $request->attributes->get('_route');
        $this->logger->info('Stored route in session:', ['stuff_route' => $stuff_route]);
        $this->logger->info('isXmlHttpRequest', [$request->isXmlHttpRequest()]);

        if ($request->isXmlHttpRequest() || !in_array($stuff_route, ['app_zone', 'app_productLine', 'app_category', 'app_button'])) {
            $this->logger->info('it will return without changing stuff in session cause of XHR or _route');
            return;
        }

        $session = $request->getSession();

        if (!$session->isStarted()) {
            $session->start();
        }

        if ($session->get('lastActivity')) {
            $session->remove('lastActivity', time());
        }

        $session->set('inactive', false);


        $session->set('stuff_route', $stuff_route);

        $stuff_param = $request->attributes->get('_route_params');
        $session->set('stuff_param', $stuff_param);
        $this->logger->info('Stored route and parameters in session', [
            'stuff_route' => $stuff_route,
            'stuff_param' => $stuff_param,
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -2],
        ];
    }
}
