<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;

class AccountService
{
    private $passwordHasher;
    private $userRepository;
    private $manager;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $manager
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
    }

    public function createAccount(Request $request, &$error, $currentRoute, $routeParams)
    {
        if ($request->getMethod() == 'POST') {
            $name = $request->request->get('username');
            $password = $request->request->get('password');
            $role = $request->request->get('role');

            // check if the username is already in use
            $user = $this->userRepository->findOneBy(['username' => $name]);
            if ($user) {
                $error = 'This username is already in use';
            } else {
                // create the user
                $user = new User();
                $password = $this->passwordHasher->hashPassword($user, $password);
                $user->setUsername($name);
                $user->setPassword($password);
                $user->setRoles([$role]);
                $this->manager->persist($user);
                $this->manager->flush();

                return  ['user' => $user, 'route' => $currentRoute, 'params' => $routeParams];
            }
        }

        return null;
    }
}