<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Entity\Department;

use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

use App\Service\AccountService;
use App\Service\EntityFetchingService;
use App\Service\EntityDeletionService;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Routing\Annotation\Route;



/**
 * AccountController
 *
 * This controller manages user account operations including creation, modification,
 * deletion, blocking/unblocking, and department management. It provides functionality
 * for administrators to manage user accounts and departments within the system.
 */
#[Route('/account', name: 'app_account_')]
class AccountController extends AbstractController
{
    /**
     * @var LoggerInterface Logger for recording account-related operations
     */
    private $logger;

    /**
     * @var EntityManagerInterface Doctrine entity manager for database operations
     */
    private $em;

    /**
     * @var UserRepository Repository for User entity operations
     */
    private $userRepository;

    /**
     * @var DepartmentRepository Repository for Department entity operations
     */
    private $departmentRepository;

    /**
     * @var AccountService Service for account management operations
     */
    private $accountService;

    /**
     * @var EntityFetchingService Service for fetching entities
     */
    private $entityFetchingService;

    /**
     * @var EntityDeletionService Service for entity deletion operations
     */
    private $entityDeletionService;

    /**
     * Constructor for AccountController
     *
     * Initializes all required services and repositories for account management.
     *
     * @param LoggerInterface $logger Logger for recording operations
     * @param EntityManagerInterface $em Entity manager for database operations
     * @param UserRepository $userRepository Repository for User entity operations
     * @param DepartmentRepository $departmentRepository Repository for Department entity operations
     * @param AccountService $accountService Service for account management
     * @param EntityFetchingService $entityFetchingService Service for fetching entities
     * @param EntityDeletionService $entityDeletionService Service for entity deletion
     */
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        DepartmentRepository $departmentRepository,
        AccountService $accountService,
        EntityFetchingService $entityFetchingService,
        EntityDeletionService $entityDeletionService,
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->departmentRepository = $departmentRepository;
        $this->accountService = $accountService;
        $this->entityFetchingService = $entityFetchingService;
        $this->entityDeletionService = $entityDeletionService;
    }

    /**
     * Creates a new user account
     *
     * This method handles both the display of the account creation form (GET)
     * and the processing of submitted form data (POST). Only accessible to super admins.
     *
     * @param Request $request The HTTP request
     * @return Response The rendered form or a redirect after account creation
     */
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
            '/services/accountservices/create_account.html.twig',
            [
                'departments' => $this->entityFetchingService->getDepartments(),
                'users'       => $this->entityFetchingService->getUsers(),
            ]
        );
    }

    /**
     * Renders and processes the account modification interface
     *
     * This method handles the display of the account modification form (GET)
     * and delegates to modifyAccountPost for processing form submissions (POST).
     *
     * @param int $userId The ID of the user account to modify
     * @param Request $request The HTTP request
     * @return Response The rendered form or a redirect after modification
     */
    #[Route(path: '/modify_account/{userId}', name: 'modify_account')]
    public function modifyAccountGet(int $userId, Request $request): Response
    {
        if (!($user = $this->userRepository->find($userId))) {
            $this->addFlash('warning', 'This User does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
        if ($request->isMethod('GET')) {
            return $this->render('services/accountservices/modify_account_view.html.twig', [
                'user' => $user
            ]);
        } else {
            return $this->modifyAccountPost($user, $request);
        }
    }

    /**
     * Processes account modification form submissions
     *
     * This method handles the business logic for modifying user accounts,
     * with special protection for super admin accounts.
     *
     * @param User $user The user entity to modify
     * @param Request $request The HTTP request containing form data
     * @return Response A redirect response after processing
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
            $this->addFlash('danger', 'Error during ' . $user->getUsername() . 'account modification. ' . $e->getMessage());
        } finally {
            return $this->redirectToRoute('app_account_modify_account', ['userId' => $user->getId()]);
        }
    }

    /**
     * Blocks a user account
     *
     * This method blocks a user account when it cannot be deleted due to
     * existing relationships with other entities in the system.
     *
     * @param Request $request The HTTP request containing the user ID
     * @return Response A redirect to the originating page with status message
     */
    #[Route(path: '/delete_account/block', name: 'block_account')]
    public function blockAccount(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        try {
            $this->accountService->blockUser($userId);
            $this->addFlash('danger', 'Le compte a été bloqué, il ne peut pas être supprimé car il est lié à des incidents, des uploads, des validations ou des approbations.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être bloqué : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }

    /**
     * Unblocks a previously blocked user account
     *
     * This method reactivates a blocked user account, requiring
     * password and role reassignment afterward.
     *
     * @param Request $request The HTTP request containing the user ID
     * @return Response A redirect to the originating page with status message
     */
    #[Route(path: '/delete_account/unblock_account', name: 'unblock_account')]
    public function unblockAccount(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        try {
            $this->accountService->unblockUser($userId);
            $this->addFlash('success', 'Le compte a été débloqué, vous devez réaffecter un Mot de passe et un Role à l\'utilisateur.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être débloqué : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }

    /**
     * Deletes a user account
     *
     * This method permanently removes a user account from the system,
     * with appropriate error handling for cases where deletion is not possible.
     *
     * @param Request $request The HTTP request containing the user ID
     * @return Response A redirect to the originating page with status message
     */
    #[Route(path: '/delete_account', name: 'delete_account')]
    public function deleteAccount(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        $username = $this->userRepository->find($userId)->getUsername();
        try {
            $this->accountService->deleteUser($userId);
            $this->addFlash('success', 'Le compte de ' . $username . ' a été supprimé');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le compte ne peut pas être supprimé : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }

    /**
     * Creates a new department
     *
     * This method handles AJAX requests to create a new department,
     * with validation to prevent duplicates and empty names.
     *
     * @param Request $request The HTTP request containing department data
     * @return JsonResponse JSON response indicating success or failure with a message
     */
    #[Route('/department/department_creation', name: 'department_creation')]
    public function departmentCreation(Request $request): JsonResponse
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        // Get the name of the department
        $departmentName = $data['department_name'] ?? null;

        // Get the existing department name
        $existingDepartment = $this->departmentRepository->findOneBy(['name' => $departmentName]);

        // Check if the department name is empty or if the department already exists
        if (empty($departmentName)) {
            return new JsonResponse(['success' => false, 'message' => 'Le nom du service ne peut pas être vide']);
        }
        if ($existingDepartment) {
            return new JsonResponse(['success' => false, 'message' => 'Ce service existe déjà']);
            // If the department does not exist, we create it
        } else {
            $department = new Department();
            $department->setName($departmentName);
            $this->em->persist($department);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Le service a été créé']);
        }
    }
    /**
     * Deletes a department
     *
     * This method removes a department from the system using the entity deletion service.
     * It provides appropriate feedback messages for both successful deletion and cases
     * where the department doesn't exist or cannot be deleted.
     *
     * @param int $departmentId The ID of the department to delete
     * @param Request $request The HTTP request
     * @return Response A redirect to the originating page with status message
     */
    #[Route('/department/department_deletion/{departmentId}', name: 'department_deletion')]
    public function departmentDeletion(int $departmentId, Request $request): Response
    {
        $entityType = "department";
        $response = $this->entityDeletionService->deleteEntity($entityType, $departmentId);
        $originUrl = $request->headers->get('referer');

        if ($response) {
            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirect($originUrl);
        }
    }

    /**
     * Transfers a user's work to another user
     *
     * This method facilitates the transfer of responsibilities, uploads, validations,
     * and other work items from one user to another before account deletion.
     * It's an important step to maintain data integrity when removing user accounts
     * that have associated work items in the system.
     *
     * @param Request $request The HTTP request containing transfer details
     * @param int $userId The ID of the user whose work is being transferred
     * @return Response A redirect with status message after the transfer operation
     */
    #[Route('/delete_account/transfer_work/{userId}', name: 'transfer_work')]
    public function transferWork(Request $request, int $userId): Response
    {
        $originUrl = $request->headers->get('Referer');
        try {
            $this->accountService->transferWork($request, $userId);
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le travail n\'a pas pu être transféré : ' . $e->getMessage());
            return $this->redirect($originUrl);
        }
        $this->addFlash('success', 'Le travail a été transféré');
        return $this->redirectToRoute('app_super_admin');
    }
}
