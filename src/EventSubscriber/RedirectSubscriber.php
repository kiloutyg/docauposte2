<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

use App\Repository\ApprobationRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;


class RedirectSubscriber implements EventSubscriberInterface
{
    private $router;
    private $security;
    private $approbationRepository;
    private $userRepository;
    private $uploadRepository;

    public function __construct(
        RouterInterface $router,
        Security $security,
        ApprobationRepository $approbationRepository,
        UserRepository $userRepository,
        UploadRepository $uploadRepository
    ) {
        $this->router = $router;
        $this->security = $security;
        $this->approbationRepository = $approbationRepository;
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
    }
    public function approbationOnKernelRequest(RequestEvent $event): void
    {
        $currentUser = $this->security->getUser();
        $currentApprobation = [];

        $request = $event->getRequest();
        $currentRoute = $request->get('_route');


        if ($currentUser && $currentRoute !== 'app_validation' && $currentRoute == 'app_base') {
            $user = $this->userRepository->find($currentUser);
            $currentApprobation = $user->getApprobations();

            if (count($currentApprobation) !== null) {
                foreach ($currentApprobation as $approbation) {
                    if ($approbation->isApproval() === null) {
                        $event->setResponse(new RedirectResponse($this->router->generate('app_validation_approbation', [
                            'approbationId' => $approbation->getId(),
                        ])));
                        return;
                    }
                }
            }
            if ($currentRoute !== 'app_base') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_base')));
            }
        }
    }
    public function reviseApprobationOnKernelRequest(RequestEvent $event): void
    {
        $currentUser = $this->security->getUser();

        $request = $event->getRequest();
        $currentRoute = $request->get('_route');

        // Skip redirection if the current route is for approbation revision
        if ($currentUser && $currentRoute !== 'app_validation_disapproved_modify' && $currentRoute == 'app_base') {
            $disapprovedUploadsbyUser = $this->uploadRepository->findBy(['uploader' => $currentUser, 'validated' => false]);

            foreach ($disapprovedUploadsbyUser as $upload) {
                $validationId = $upload->getValidation()->getId();
                $disapproval = $this->approbationRepository->findOneBy(['Validation' => $validationId, 'Approval' => false]);
                if ($disapproval === null) {
                    break;
                }
                $disapprovalId = $disapproval->getId();

                $event->setResponse(new RedirectResponse($this->router->generate('app_validation_disapproved_modify', [
                    'approbationId' => $disapprovalId,
                ])));
                return;
            }
            if ($currentRoute !== 'app_base') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_base')));
            }
        }
    }


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