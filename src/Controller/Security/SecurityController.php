<?php

namespace App\Controller\Security;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * SecurityController
 *
 * This controller manages the authentication logic of the application,
 * including user login and logout processes. It integrates with Symfony's
 * security system to handle authentication and session management.
 */
class SecurityController extends AbstractController
{
    /**
     * Handles user login process
     *
     * This method renders the login form and processes login attempts.
     * If a user is already authenticated, they are redirected to the homepage.
     * Otherwise, the login form is displayed with any authentication errors.
     *
     * @param AuthenticationUtils $authenticationUtils Symfony service for authentication utilities
     * @return \Symfony\Component\HttpFoundation\Response The rendered login form or a redirect response
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // If user is already logged in, redirect to homepage
        if ($this->getUser()) {
            $this->addFlash('success', 'Vous êtes connecté');
            return $this->redirectToRoute('app_base');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // Get the last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Render the login form with context variables
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
            'user'          => $this->getUser()
        ]);
    }

    /**
     * Handles user logout process
     *
     * This function is responsible for rendering the logout interface.
     * It adds a flash message to notify the user of successful logout.
     * The actual logout functionality is handled by Symfony's security system.
     *
     * @return void No return value as the security system handles the redirect
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        $this->addFlash('success', 'Vous êtes déconnecté');
    }
}
