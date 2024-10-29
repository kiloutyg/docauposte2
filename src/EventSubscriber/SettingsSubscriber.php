<?php
// src/EventSubscriber/SettingsSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Service\CacheService; // Or use SettingsRepository

class SettingsSubscriber implements EventSubscriberInterface
{
    private $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // $controller can be a class or a Closure
        if (!is_array($controller)) {
            return;
        }

        // Check if the controller is an instance of ValidationController
        if ($controller[0] instanceof \App\Controller\ValidationController) {
            $settings = $this->cacheService->settings;

            if (!$settings || !$settings->isUploadValidation()) {
                // Deactivate the controller
                throw new NotFoundHttpException('Validation is not activated');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onKernelController',
        ];
    }
}

?>
<!-- 
// src/EventSubscriber/ValidationSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\RouterInterface;
use App\Service\CacheService;

class SettingsSubscriber implements EventSubscriberInterface
{


    private $cacheService;
    private $requestStack;
    private $router;

    public function __construct(
        CacheService $cacheService,
        RequestStack $requestStack,
        RouterInterface $router,
    )
    {
        $this->cacheService = $cacheService;
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        // $controller can be a class or a Closure
        if (!is_array($controller)) {
            return;
        }

        // Check if the controller is an instance of ValidationController
        if ($controller[0] instanceof \App\Controller\ValidationController) {
            $settings = $this->cacheService->settings;

            if (!$settings || !$settings->isUploadValidation()) {
                // Deactivate the controller
                // throw new NotFoundHttpException('Validation is not activated');

                // Access the session from the RequestStack
                $session = $this->requestStack->getSession();

                // Add flash message using the session's FlashBag
                $session->getFlashBag()->add('error', 'Validation is not activated');

                // Generate the URL to redirect to
                $redirectUrl = $this->router->generate('app_base');

                // Create a RedirectResponse
                $response = new RedirectResponse($redirectUrl);

                // Set the response on the event to redirect
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onKernelController',
        ];
    }

} -->