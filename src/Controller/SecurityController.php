<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\User;

use App\Repository\UserRepository;

use App\Service\AccountService;

class SecurityController extends BaseController
{


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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'Vous êtes déconnecté');
    }



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

    #[Route(path: '/modify_account', name: 'app_modify_account')]
    public function modify_account(AccountService $accountService, UserInterface $currentUser, AuthenticationUtils $authenticationUtils, Request $request)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $usermod = $accountService->modifyAccount($request, $currentUser);

        if ($usermod instanceof User) {
            $this->addFlash('success', 'Le compte' . $usermod->getUsername() . ' a été modifié');
            return $this->redirectToRoute('app_super_admin');
        };
        return $this->redirectToRoute('app_super_admin');
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

        $this->addFlash('success',  'Le compte a été supprimé');

        return $this->redirectToRoute('app_login');
    }
}