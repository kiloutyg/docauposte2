<?php

namespace App\Controller\Security;

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

/**
 * AccountController
 *
 * This controller manages user account operations including creation, modification,
 * deletion, blocking/unblocking, and work transfer.
 */
class AccountController extends AbstractController
{
    private $logger;
    private $em;
    private $userRepository;
    private $accountService;
    private $entityFetchingService;
    private $entityDeletionService;


    /**
     * Constructor for AccountController
     *
     * Initializes the controller with required dependencies for account management operations.
     *
     * @param LoggerInterface $logger For logging system events and errors
     * @param EntityManagerInterface $em Doctrine entity manager for database operations
     * @param UserRepository $userRepository Repository for user entity operations
     * @param AccountService $accountService Service handling account-related business logic
     * @param EntityFetchingService $entityFetchingService Service for retrieving entities from the database
     * @param EntityDeletionService $entityDeletionService Service for safely deleting entities
     */
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



    /**
     * Creates a new user account (accessible to super admin)
     *
     * This function handles both GET and POST requests for account creation.
     * For GET requests, it renders the account creation form with a list of existing users.
     * For POST requests, it processes the submitted form data to create a new user account.
     * If the account creation is successful, a success message is displayed.
     * If an error occurs during account creation, an error message with details is shown.
     *
     * @param Request $request The HTTP request object containing either form data (POST) or just the request (GET)
     * @return Response A response object containing either the rendered form (GET) or a redirect to the super admin dashboard (POST)
     * @throws \Exception If the account creation fails (caught internally)
     */
    #[Route(path: '/account/create', name: 'app_account_create')]
    public function createAccountController(Request $request): Response
    {

        if ($request->isMethod('GET')) {
            return $this->renderAccountCreationTemplate();
        }

        try {
            $this->accountService->createAccount($request);
            $this->addFlash('success', 'Le compte a bien été créé.');
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
        return $this->renderAccountCreationTemplate(keepOpen: true);
    }




    /**
     * Renders the account modification interface for super admin
     *
     * This function handles both GET and POST requests for account modification.
     * For GET requests, it renders the account modification form with the user's current data.
     * For POST requests, it delegates to the modifyAccountPost method to process the form submission.
     * If the requested user doesn't exist, it redirects to the super admin dashboard with a warning.
     *
     * @param int $userId The ID of the user account to be modified
     * @param Request $request The HTTP request object containing either form data (POST) or just the request (GET)
     * @return Response A response object containing either the rendered form (GET) or a redirect (POST/error)
     */
    #[Route(path: '/account/modify_account/{userId}', name: 'app_account_modify_account')]
    public function modifyAccountGet(int $userId, Request $request): Response
    {
        if (!($user = $this->userRepository->find($userId))) {
            $this->addFlash('warning', 'This User does not exist');
            return $this->render(
                '/services/account_services/create_account.html.twig',
                [
                    'users' => $this->userRepository->getAllUsersOrderedByLastname(),
                ]
            );
        }
        if ($request->isMethod('GET')) {
            return $this->render('services/account_services/modify_account_view.html.twig', [
                'user' => $user,
                'operatorSuggestions' => $this->entityFetchingService->getOperatorSuggestionByUsername($user->getUsername()) ?? null,
            ]);
        } else {
            return $this->modifyAccountPost($user, $request);
        }
    }

    /**
     * Processes the account modification request
     *
     * This function handles the processing of user account modification requests.
     * It prevents modification of super admin accounts for security reasons.
     * The function uses the accountService to perform the actual modification
     * and provides appropriate feedback via flash messages.
     *
     * @param User $user The user entity whose account is being modified
     * @param Request $request The HTTP request containing the modification data
     * @return Response A redirect response to the account modification page with appropriate flash messages
     * @throws \Exception If the account modification fails (caught internally)
     */
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
            $this->addFlash('danger', 'Error during ' . $user->getUsername() . ' account modification. ' . $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_account_modify_account', ['userId' => $user->getId()]);
        }
    }

    /**
     * Blocks a user account
     *
     * This function handles the blocking of a user account in the system.
     * Blocking is used when an account cannot be deleted due to existing relationships
     * with incidents, uploads, validations, or approvals. The blocked account
     * remains in the system but cannot be used for authentication.
     *
     * @param Request $request The HTTP request object containing the userId in the query parameters
     * @return Response A redirect response to the super admin dashboard with appropriate flash messages
     * @throws \Exception If the account cannot be blocked (caught internally)
     */
    #[Route(path: '/account/delete_account/block', name: 'app_account_block_account')]
    public function blockAccount(Request $request): Response
    {
        try {
            $this->accountService->blockUser($this->userRepository->find($request->query->get('userId')));
            $this->addFlash('danger', 'Le compte a été bloqué, il ne peut pas être supprimé car il est lié à des incidents, des uploads, des validations ou des approbations.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être bloqué : ' . $e->getMessage());
        }
        return $this->renderAccountCreationTemplate(keepOpen: true);
    }

    /**
     * Unblocks a user account
     *
     * This function reactivates a previously blocked user account in the system.
     * After unblocking, the account will need to have a new password and role assigned.
     * The function uses the accountService to perform the actual unblocking operation.
     * If successful, a success message is displayed. If the unblocking fails,
     * an error message with the exception details is shown.
     *
     * @param Request $request The HTTP request object containing the userId in the query parameters
     * @return Response A redirect response to the super admin dashboard with appropriate flash messages
     * @throws \Exception If the account cannot be unblocked (caught internally)
     */
    #[Route(path: '/account/delete_account/unblock_account', name: 'app_account_unblock_account')]
    public function unblockAccount(Request $request): Response
    {
        $user = $this->userRepository->find($request->query->get('userId'));
        try {
            $this->accountService->unblockUser($user);
            $this->addFlash('success', 'Le compte a été débloqué, vous devez réaffecter un Mot de passe et un Role à l\'utilisateur.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être débloqué : ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_account_modify_account', ['userId' => $user->getId()]);
    }

    /**
     * Deletes a user account
     *
     * This function handles the deletion of a user account from the system.
     * It uses the entityDeletionService to perform the actual deletion operation.
     * If successful, a success message is displayed. If the deletion fails,
     * an error message with the exception details is shown.
     *
     * @param Request $request The HTTP request object containing the userId in the query parameters
     * @return Response A redirect response to the super admin dashboard with appropriate flash messages
     */
    #[Route(path: '/account/delete_account', name: 'app_account_delete_account')]
    public function deleteAccount(Request $request): Response
    {
        try {
            $this->entityDeletionService->deleteEntity('user', $request->query->get('userId'));
            $this->addFlash('success', 'Le compte a été supprimé');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être supprimé : ' . $e->getMessage());
        }
        return $this->renderAccountCreationTemplate(keepOpen: true);
    }

    /**
     * Transfers work from one user to another before account deletion
     *
     * This function handles the transfer of all work items (incidents, uploads, validations, etc.)
     * from one user to another. It's typically used before deleting a user account to ensure
     * continuity of work and prevent data loss.
     *
     * @param Request $request The HTTP request object containing the recipient user ID in the 'work-transfer-recipient' field
     * @param int $userId The ID of the user whose work is being transferred (source user)
     * @return Response A redirect response to the super admin dashboard with appropriate flash messages
     * @throws \Exception If the work transfer fails for any reason (caught internally)
     */
    #[Route('/account/delete_account/transfer_work/{userId}', name: 'app_account_transfer_work')]
    public function transferWork(Request $request, int $userId): Response
    {
        $originalUser = $this->userRepository->find($userId);
        $recipient = $this->userRepository->find($request->request->get('work-transfer-recipient'));
        try {
            $this->accountService->transferWork($originalUser, $recipient);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le travail n\'a pas pu être transféré : ' . $e->getMessage());
        }
        $this->addFlash('success', 'Le travail a été transféré');
        return $this->renderAccountCreationTemplate(keepOpen: true);
    }



    /**
     * Renders the account creation template with a list of all users
     *
     * This helper method renders the account creation template with data needed for
     * displaying the user management interface. It fetches all users from the repository
     * ordered by lastname and passes them to the template.
     *
     * @param bool $keepOpen Whether to keep the accordion open
     * @return Response A response object containing the rendered account creation template
     */
    private function renderAccountCreationTemplate(bool $keepOpen = false): Response
    {
        $params = [
            'users' => $this->userRepository->getAllUsersOrderedByLastname(),
        ];

        if ($keepOpen) {
            $params['openAccordion'] = true;
        }

        return $this->render(
            '/services/account_services/create_account.html.twig',
            $params
        );
    }
}
