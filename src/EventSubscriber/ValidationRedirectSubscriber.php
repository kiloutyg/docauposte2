<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Bundle\SecurityBundle\Security;

use App\Repository\UserRepository;
use App\Repository\UploadRepository;

use App\Service\SettingsService;


class ValidationRedirectSubscriber implements EventSubscriberInterface
{
    private $router;


    private $security;

    private $userRepository;
    private $uploadRepository;
    private $settingsService;

    public function __construct(
        RouterInterface $router,

        Security $security,

        UserRepository $userRepository,
        UploadRepository $uploadRepository,
        SettingsService $settingsService
    ) {
        $this->router = $router;


        $this->security = $security;

        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->settingsService = $settingsService;
    }

    public function approbationOnKernelRequest(RequestEvent $event): void
    {


        if (!$this->settingsService->getSettings()->isUploadValidation()) {
            return;
        }

        // Get the current user
        $currentUser = $this->security->getUser();

        // Initialize an empty array to store current approbations
        $currentApprobation = [];

        // Get the request object from the event
        $request = $event->getRequest();

        // Get the current route name from the request object
        $currentRoute = $request->get('_route');

        // Check if there is a current user and the current route is not 'app_validation' and it is 'app_base'
        if ($currentUser && $currentRoute !== 'app_validation' && $currentRoute == 'app_base') {
            // Find the user object using the user id
            $user = $this->userRepository->find($currentUser);

            // Get the current user's approbations
            $currentApprobation = $user->getApprobations();

            // Check if the count of current approbations is not null
            if (count($currentApprobation) !== null) {
                // Iterate over each approbation
                foreach ($currentApprobation as $approbation) {
                    // If the approbation is not approved, redirect to the 'app_validation_approbation' route
                    if ($approbation->isApproval() === null) {
                        $event->setResponse(new RedirectResponse($this->router->generate('app_validation_approbation', [
                            'approbationId' => $approbation->getId(),
                        ])));
                        return;
                    }
                }
            }

            // If the current route is not 'app_base', redirect to the 'app_base' route
            if ($currentRoute !== 'app_base') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_base')));
            }
        }
    }

    public function reviseApprobationOnKernelRequest(RequestEvent $event): void
    {
        if (!$this->settingsService->getSettings()->isUploadValidation()) {
            return;
        }

        // Get the current user
        $currentUser = $this->security->getUser();

        // Get the request object from the event
        $request = $event->getRequest();

        // Get the current route name from the request object
        $currentRoute = $request->get('_route');

        // Check if there is a current user and the current route is not 'app_validation_disapproved_modify_by_upload' and it is 'app_base'
        if ($currentUser && $currentRoute !== 'app_validation_disapproved_modify_by_upload' && $currentRoute == 'app_base') {
            // Find any disapproved uploads by the current user
            $disapprovedUploadsbyUser = $this->uploadRepository->findBy(['uploader' => $currentUser, 'validated' => false]);

            // Iterate over each disapproved upload
            foreach ($disapprovedUploadsbyUser as $upload) {
                $event->setResponse(new RedirectResponse($this->router->generate(
                    'app_validation_disapproved_modify_by_upload',
                    ['uploadId' => $upload->getId(),]
                )));
                return;
            }

            // If the current route is not 'app_base', redirect to the 'app_base' route
            if ($currentRoute !== 'app_base') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_base')));
            }
        }
    }

    // This method returns an array of events and their associated handlers for this class
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['approbationOnKernelRequest', 0],
                ['reviseApprobationOnKernelRequest', 1]
            ],
        ];
    }
}
