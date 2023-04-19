<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\User;

use App\Service\AccountService;

class SecurityController extends BaseController
{


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            $this->addFlash('success', 'You have been logged in');
            // return $this->redirectToRoute('app_base');

            // Fallback to a default route if the previous URL is not set in the session
            return $this->redirectToRoute('app_base');
        }

        $error        = $authenticationUtils->getLastAuthenticationError(); // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'users'        => $this->userRepository->findAll(),
            'error' => $error,
            'user' => $this->getUser()
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'You have been logged out');
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    public function create_account(AccountService $accountService, AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $accountService->createAccount($request, $error);

        if ($user) {
            $this->authenticateUser($user);
            $this->addFlash('success', 'Your account has been created');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('services/create_account.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'user' => $this->getUser(),
        ]);
    }

    private function authenticateUser(User $user)
    {
        $providerKey = 'secured_area'; // your firewall name
        $token       = new UsernamePasswordToken($user, $providerKey, $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
    }

    #[Route(path: '/delete_account', name: 'app_delete_account')]
    public function delete_account(AccountService $accountService, Request $request): Response
    {
        $id = $request->query->get('id');
        $accountService->deleteUser($id);

        $this->addFlash('success', 'Your account has been deleted');

        return $this->redirectToRoute('app_login');
    }
}