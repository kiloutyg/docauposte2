<?php
// src/EventSubscriber/SettingsSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

// use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse; // we need to return a response
use Symfony\Component\Routing\RouterInterface;

use App\Service\SettingsService;

class SettingsSubscriber implements EventSubscriberInterface
{
    private $settingsService;
    private $router;

    public function __construct(
        RouterInterface $router,
        SettingsService $settingsService,
    ) {
        $this->settingsService = $settingsService;
        $this->router = $router;
    }

    public function onKernelController(
        ControllerEvent $controllerEvent,
    ) {
        $controller = $controllerEvent->getController();

        // $controller can be a class or a Closure
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        // Check if the controller is an instance of ValidationController
        if ($controller instanceof \App\Controller\ValidationController) {

            if (!$this->settingsService->getSettings() || !$this->settingsService->getSettings()->isUploadValidation()) {
                // Get the request
                $request = $controllerEvent->getRequest();

                // Access the session from the RequestStack
                $session = $request->getSession();

                // Add flash message using the session's FlashBag
                $session->getFlashBag()->add('error', 'Validation is not activated');

                // Deactivate the controller
                // throw new AccessDeniedHttpException('Validation is not activated');
                $request->attributes->set('disabled_controller', true);
            }
        }

        // Check if the controller is an instance of TrainingRecordController
        if ($controller instanceof \App\Controller\TrainingRecordController) {

            if (!$this->settingsService->getSettings() || !$this->settingsService->getSettings()->IsTraining()) {
                // Get the request
                $request = $controllerEvent->getRequest();

                // Access the session from the RequestStack
                $session = $request->getSession();

                // Add flash message using the session's FlashBag
                $session->getFlashBag()->add('error', 'Training is not activated');

                // Deactivate the controller
                // throw new AccessDeniedHttpException('Training is not activated');
                $request->attributes->set('disabled_controller', true);
            }
        }

        // Check if the controller is an instance of OperatorController
        if ($controller instanceof \App\Controller\OperatorController) {

            if (!$this->settingsService->getSettings() || !$this->settingsService->getSettings()->IsTraining()) {
                // Get the request
                $request = $controllerEvent->getRequest();

                // Access the session from the RequestStack
                $session = $request->getSession();

                // Add flash message using the session's FlashBag
                $session->getFlashBag()->add('error', 'Operator training is not activated');

                // Deactivate the controller
                // throw new AccessDeniedHttpException('Operator training is not activated');
                $request->attributes->set('disabled_controller', true);
            }
        }
    }

    public function onKernelControllerResponse(ResponseEvent $responseEvent)
    {
        $request = $responseEvent->getRequest();
        if ($request->attributes->get('disabled_controller')) {
            $responseEvent->setResponse(new RedirectResponse($this->router->generate('app_base')));
            return;
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onKernelController',
            ResponseEvent::class => 'onKernelControllerResponse',
        ];
    }
}
