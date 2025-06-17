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

        // Skip AJAX inactivity checks
        if (
            $request->getPathInfo() === '/inactivity_check' ||
            $request->getPathInfo() === '/cycling_incident' ||
            $request->getPathInfo() === '/api/settings'  ||
            $request->getPathInfo() === '/api/user_data'  ||
            $request->getPathInfo() === '/api/entity_data'
        ) {
            return;
        }
        $stuff_route = $request->attributes->get('_route');

        if (
            $request->isXmlHttpRequest() ||
            !in_array($stuff_route, [
                'app_zone',
                'app_productLine',
                'app_category',
                'app_button'
            ])
        ) {
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
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -2],
        ];
    }
}
