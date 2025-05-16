<?php

namespace App\Security;

use App\Repository\UserRepository;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\SecurityBundle\Security;

// Manage the authentication of the user using the login form
class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private $urlGenerator;
    private $security;
    private $userRepository;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        UserRepository $userRepository,
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $name = $request->request->get('name', '');
        $password = $request->request->get('password', '');
        // Immediately create credentials and clear from request
        $credentials = new PasswordCredentials($password);
        $request->request->set('password', '[REDACTED]');

        return new Passport(
            new UserBadge($name),
            $credentials,
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_base'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
