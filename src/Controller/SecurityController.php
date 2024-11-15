<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\HttpFoundation\JsonResponse;

use App\Entity\User;
use App\Entity\Department;


// This controller manage the logic of the security interface
class SecurityController extends FrontController
{

    // This function is responsible for rendering the login interface 
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            $this->addFlash('success', 'Vous êtes connecté');
            return $this->redirectToRoute('app_base');
        }

        $error        = $authenticationUtils->getLastAuthenticationError(); // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'user'          => $this->getUser()
        ]);
    }

    // This function is responsible for rendering the logout interface
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'Vous êtes déconnecté');
    }

    // This function is responsible for rendering the account modifiying interface destined to the super admin
    #[Route(path: '/modify_account/{userId}', name: 'app_modify_account')]
    public function modify_account(UserInterface $currentUser, int $userId, AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $user = $this->userRepository->find($userId);
        $originUrl = $request->headers->get('Referer');

        if ($request->isMethod('GET')) {
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $this->addFlash('danger', 'Le compte ne peut être modifié');
                return $this->redirect($originUrl);
            }
            return $this->render('services/accountservices/modify_account_view.html.twig', [
                'user'          => $user,
                'error'         => $error,
            ]);
        } else if ($request->isMethod('POST')) {

            $error = $authenticationUtils->getLastAuthenticationError();
            $usermod = $this->accountService->modifyAccount($request, $currentUser, $user);

            if ($usermod instanceof User) {
                $this->addFlash('success', 'Le compte ' . $usermod->getUsername() . ' a été modifié');
                return $this->redirectToRoute('app_super_admin');
            };
            return $this->redirectToRoute('app_super_admin');
        }
    }


    // This function is managing the logic of authentication
    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token       = new UsernamePasswordToken($user, $providerKey, $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
    }

    // This function is responsible for managing the logic of the account deletion
    #[Route(path: '/delete_account/basic', name: 'app_delete_account_basic')]
    public function delete_account_basic(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        try {
            $this->accountService->blockUser($userId);
            $this->addFlash('danger',  'Le compte a été bloqué, il ne peut pas être supprimé car il est lié à des incidents, des uploads, des validations ou des approbations.');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être bloqué : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }

    // This function is responsible for managing the unblocking of an account
    #[Route(path: '/delete_account/unblock_account', name: 'app_unblock_account')]
    public function unblock_account(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        try {
            $this->accountService->unblockUser($userId);
            $this->addFlash('success',  'Le compte a été débloqué, vous devez réaffecter un Mot de passe et un Role à l\'utilisateur.');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être débloqué : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }

    // This function is responsible for managing the logic of the account deletion
    #[Route(path: '/delete_account', name: 'app_delete_account')]
    public function delete_account(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $originUrl = $request->headers->get('Referer');
        $username = $this->userRepository->find($userId)->getUsername();
        try {
            $this->accountService->deleteUser($userId);
            $this->addFlash('success',  'Le compte de ' . $username . ' a été supprimé');
        } catch (\Exception $e) {
            $this->addFlash('danger',  'Le compte ne peut pas être supprimé : ' . $e->getMessage());
        }
        return $this->redirect($originUrl);
    }


    // Logic to create a new department and display a message
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
            $Department = new Department();
            $Department->setName($departmentName);
            $this->em->persist($Department);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Le service a été créé']);
        }
    }

    // Create a route for department deletion. It depends on the entitydeletionService.
    #[Route('/department/department_deletion/{departmentId}', name: 'app_department_deletion')]
    public function departmentDeletion(int $departmentId, Request $request): Response
    {
        $entityType = "department";
        $entity = $this->departmentRepository->find($departmentId);
        $response = $this->entitydeletionService->deleteEntity($entityType, $departmentId);
        $originUrl = $request->headers->get('referer');

        if ($response == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirect($originUrl);
        }
    }

    // Create a route to allow transmission of work to another user before deleting the account with the delete_account method
    #[Route('/delete_account/transfer_work/{userId}', name: 'app_transfer_work')]
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
