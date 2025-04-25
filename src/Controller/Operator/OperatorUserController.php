<?php

namespace App\Controller\Operator;

use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Contracts\Cache\CacheInterface;

use App\Repository\UploadRepository;
use App\Repository\ValidationRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\UserRepository;

use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;
use App\Service\TrainingRecordService;
use App\Service\PdfGeneratorService;
use App\Service\OperatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OperatorUserController extends AbstractController
{

    public $em;
    public $request;
    public $logger;
    public $authChecker;
    public $uapRepository;
    public $teamRepository;
    public $operatorRepository;
    public $userRepository;



    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,
        RequestStack                    $requestStack,

        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        UserRepository                  $userRepository,

    ) {

        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;
        $this->request                      = $requestStack->getCurrentRequest();

        $this->uapRepository                = $uapRepository;
        $this->teamRepository               = $teamRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->userRepository               = $userRepository;
    }





    #[Route('/operator/user_login_check', name: 'app_operator_user_login_check')]
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
