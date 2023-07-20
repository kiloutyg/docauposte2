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

use App\Service\AccountService;

// This controller manage the logic of the security interface
class SecurityController extends BaseController
{

    // This function is responsible for rendering the login interface 
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request)
    {
        if ($this->getUser()) {
            $this->addFlash('success', 'Vous êtes connecté');
            return $this->redirectToRoute('app_base');
        }

        $error        = $authenticationUtils->getLastAuthenticationError(); // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'users'         => $this->userRepository->findAll(),
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
    #[Route(path: '/modify_account_view/{userid}', name: 'app_modify_account_view')]
    public function modify_account_view(int $userid, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $user = $this->userRepository->findOneBy(['id' => $userid]);

        return $this->render('services/accountservices/modify_account_view.html.twig', [
            'user'          => $user,
            'error'         => $error,
        ]);
    }

    // This function manage the logic of the account modifiying
    #[Route(path: '/modify_account', name: 'app_modify_account')]
    public function modify_account(AccountService $accountService, UserInterface $currentUser, AuthenticationUtils $authenticationUtils, Request $request)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $usermod = $accountService->modifyAccount($request, $currentUser);

        if ($usermod instanceof User) {
            $this->addFlash('success', 'Le compte ' . $usermod->getUsername() . ' a été modifié');
            return $this->redirectToRoute('app_super_admin');
        };
        return $this->redirectToRoute('app_super_admin');
    }

    // This function is managing the logic of authentication
    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token       = new UsernamePasswordToken($user, $providerKey, $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
    }

    // This function is responsible for managing the logic of the account deletion
    #[Route(path: '/delete_account', name: 'app_delete_account')]
    public function delete_account(AccountService $accountService, Request $request): Response
    {
        $id = $request->query->get('id');
        $accountService->deleteUser($id);
        // $originUrl = $request->headers->get('referer');
        $this->addFlash('success',  'Le compte a été supprimé');

        return $this->redirectToRoute('app_super_admin');
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
    #[Route('/department/department_deletion/{department}', name: 'app_department_deletion')]
    public function departmentDeletion(string $department, Request $request): Response
    {
        $entityType = "department";
        $entityid = $this->departmentRepository->findOneBy(['name' => $department]);
        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());
        $originUrl = $request->headers->get('referer');

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirect($originUrl);
        }
    }
}