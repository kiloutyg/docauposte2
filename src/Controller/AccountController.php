<?php

namespace App\Controller;

use App\Entity\User;

use App\Repository\UserRepository;

use App\Service\AccountService;
use App\Service\EntityFetchingService;
use App\Service\EntityDeletionService;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/account', name: 'app_account_')]
class AccountController extends AbstractController
{
    private $logger;

    private $em;

    private $userRepository;
    private $accountService;
    private $entityFetchingService;
    private $entityDeletionService;

    public function __construct(

        LoggerInterface $logger,

        EntityManagerInterface $em,

        UserRepository $userRepository,

        AccountService $accountService,
        EntityFetchingService $entityFetchingService,
        EntityDeletionService $entityDeletionService,

    ) {
        $this->logger = $logger;

        $this->em = $em;

        $this->userRepository = $userRepository;

        $this->accountService = $accountService;
        $this->entityFetchingService = $entityFetchingService;
        $this->entityDeletionService = $entityDeletionService;
    }




    // Creation of new user account destined to the super admin
    #[Route(path: '/create', name: 'create')]
    public function createAccountController(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $this->accountService->createAccount($request);
                $this->addFlash('success', 'Le compte a bien été créé.');
            } catch (\Exception $e) {
                // Catch and handle the exception.
                $this->addFlash('danger', $e->getMessage());
            }
            return $this->redirectToRoute('app_super_admin');
        }
        return $this->render(
            '/services/account_services/create_account.html.twig',
            [
                'users'       => $this->entityFetchingService->getUsers(),
            ]
        );
    }



    /**
     * This function is responsible for rendering the account modification interface destined to the super admin.//+
     */
    #[Route(path: '/modify_account/{userId}', name: 'modify_account')]
    public function modifyAccountGet(int $userId, Request $request): Response
    {
        if (!($user = $this->userRepository->find($userId))) {
            $this->addFlash('warning', 'This User does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
        if ($request->isMethod('GET')) {
            return $this->render('services/account_services/modify_account_view.html.twig', [
                'user'                  => $user,
                'operatorSuggestions'   => $this->entityFetchingService->getOperatorSuggestionByUsername($user->getUsername()) ?? null,
            ]);
        } else {
            return $this->modifyAccountPost($user, $request);
        }
    }


    public function modifyAccountPost(User $user, Request $request)
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $this->logger->error('SuperAdmin account modification try');
            return $this->redirectToRoute('app_super_admin');
        }
        try {
            $modified = $this->accountService->modifyAccount($request, $user);
            $this->addFlash('success', $modified . ' du compte ' . $user->getUsername() . ' a été modifié');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Error during ' . $user->getUsername() . 'account modification. ' . $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_account_modify_account', ['userId' => $user->getId()]);
        }
    }




    // This function is responsible for managing the logic of the account deletion
    #[Route(path: '/delete_account/block', name: 'block_account')]
    public function blockAccount(Request $request): Response
    {
        try {
            $this->accountService->blockUser($this->userRepository->find($request->query->get('userId')));
            $this->addFlash('danger',  'Le compte a été bloqué, il ne peut pas être supprimé car il est lié à des incidents, des uploads, des validations ou des approbations.');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être bloqué : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_super_admin');
    }





    // This function is responsible for managing the unblocking of an account
    #[Route(path: '/delete_account/unblock_account', name: 'unblock_account')]
    public function unblockAccount(Request $request): Response
    {
        try {
            $this->accountService->unblockUser($this->userRepository->find($request->query->get('userId')));
            $this->addFlash('success',  'Le compte a été débloqué, vous devez réaffecter un Mot de passe et un Role à l\'utilisateur.');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être débloqué : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_super_admin');
    }





    // This function is responsible for managing the logic of the account deletion
    #[Route(path: '/delete_account', name: 'delete_account')]
    public function deleteAccount(Request $request): Response
    {
        try {
            $this->entityDeletionService->deleteEntity('user', $request->query->get('userId'));
            $this->addFlash('success',  'Le compte de ' . $this->userRepository->find($request->query->get('userId'))->getUsername() . ' a été supprimé');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être supprimé : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_super_admin');
    }







    // Create a route to allow transmission of work to another user before deleting the account with the delete_account method
    #[Route('/delete_account/transfer_work/{userId}', name: 'transfer_work')]
    public function transferWork(Request $request, int $userId): Response
    {
        $originalUser = $this->userRepository->find($userId);
        $recipient = $this->userRepository->find($request->request->get('work-transfer-recipient'));
        try {
            $this->accountService->transferWork($originalUser, $recipient);
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le travail n\'a pas pu être transféré : ' . $e->getMessage());
            return $this->redirectToRoute('app_super_admin');
        }
        $this->addFlash('success', 'Le travail a été transféré');
        return $this->redirectToRoute('app_super_admin');
    }
}
