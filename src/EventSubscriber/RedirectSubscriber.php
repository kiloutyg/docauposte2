<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\ApprobationRepository;
use App\Repository\UserRepository;


class RedirectSubscriber implements EventSubscriberInterface
{
    private $router;
    private $security;
    private $approbationRepository;
    private $userRepository;

    public function __construct(
        RouterInterface $router,
        Security $security,
        ApprobationRepository $approbationRepository,
        UserRepository $userRepository,
    ) {
        $this->router = $router;
        $this->security = $security;
        $this->approbationRepository = $approbationRepository;
        $this->userRepository = $userRepository;
    }
    public function onKernelRequest(RequestEvent $event): void
    {
        $currentUser = $this->security->getUser();
        $currentApprobation = [];

        if ($currentUser) {
            $user = $this->userRepository->find($currentUser);
            $currentApprobation = $user->getApprobations();
            if (count($currentApprobation) > 0) {
                foreach ($currentApprobation as $approbation) {

                    $event->setResponse(new RedirectResponse($this->router->generate('app_validation', ['uploadId' => $approbation->getId()])));
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}