<?php

namespace App\Controller\Operator;

use \Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Repository\OperatorRepository;
use App\Repository\UserRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OperatorUserController extends AbstractController
{
    public $logger;
    public $authChecker;
    public $operatorRepository;
    public $userRepository;



    public function __construct(
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        OperatorRepository              $operatorRepository,
        UserRepository                  $userRepository,

    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        $this->operatorRepository           = $operatorRepository;
        $this->userRepository               = $userRepository;
    }





    #[Route('/operator/user-login-check', name: 'app_operator_user_login_check')]
    public function userLoginCheck(): JsonResponse
    {
        $currentUser = $this->getUser();
        $this->logger->info('current user', [$currentUser]);
        $this->logger->info('role granted', [$this->authChecker->isGranted('ROLE_MANAGER')]);

        if (!empty($currentUser) && $this->authChecker->isGranted('ROLE_MANAGER')) {
            $user               = $this->userRepository->find($currentUser);
            $this->logger->info(' user', [$user]);

            $operator           = $this->operatorRepository->findOneBy(['name' => $user->getUsername()]);
            $this->logger->info('operator', [$operator]);

            if ($operator != null && $operator->isIsTrainer()) {
                return new JsonResponse([
                    'found'         => true,
                    'name'          => $operator->getName(),
                    'code'          => $operator->getCode(),
                    'trainerId'     => $operator->getId(),
                    'uploadTrainer' => true,
                ]);
            }
        }
        return new JsonResponse([
            'found'         => false
        ]);
    }
}
