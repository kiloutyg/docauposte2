<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

use App\Service\EntityDeletionService;

// This class is responsible for managing the user accounts logic
class AccountService
{
    private $passwordHasher;
    private $userRepository;
    private $manager;
    private $departmentRepository;
    private $entityDeletionService;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $manager,
        DepartmentRepository $departmentRepository,
        EntityDeletionService $entityDeletionService
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
        $this->departmentRepository = $departmentRepository;
        $this->entityDeletionService = $entityDeletionService;
    }

    // This function is responsible for creating a new user account and persisting it to the database
    public function createAccount(Request $request, $error)
    {
        if ($request->getMethod() == 'POST') {
            $name = $request->request->get('username');
            $password = $request->request->get('password');
            $role = $request->request->get('role');
            $departmentId = $request->request->get('department');
            $department = $this->departmentRepository->findOneBy(['id' => $departmentId]);
            $emailAddress = $request->request->get('emailAddress');


            // check if the username is already in use
            $user = $this->userRepository->findOneBy(['username' => $name]);
            if ($user) {
                $error = 'Ce nom d\'utilisateur est déja utilisé.';
            } else {
                // create the user
                $user = new User();
                $password = $this->passwordHasher->hashPassword($user, $password);
                $user->setUsername($name);
                $user->setPassword($password);
                $user->setRoles([$role]);
                $user->setDepartment($department);
                $user->setEmailAddress($emailAddress);
                $this->manager->persist($user);
                $this->manager->flush();

                return  $user;
            }
        }
        return null;
    }

    // This function is responsible for modifying a user account and persisting the modification to the database
    public function modifyAccount(Request $request, UserInterface $currentUser, User $user)
    {
        if ($request->getMethod() == 'POST') {

            $departmentId = $request->request->get('department');
            $department = $this->departmentRepository->findOneBy(['id' => $departmentId]);
            $emailAddress = $request->request->get('emailAddress');

            // Check if the current user is a super admin
            if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {

                // Get the user to be modified
                if ($user) {
                    if ($request->request->get('username') != '') {
                        $newName = $request->request->get('username');

                        if ($this->userRepository->findOneBy(['username' => $newName])) {
                            return 'Ce nom d\'utilisateur est déja utilisé.';
                        } else {
                            $password = $user->getPassword();
                            $password = $this->passwordHasher->hashPassword($user, $password);
                            $user->setUsername($newName); // Set the new username
                            $user->setPassword($password);
                        }
                    };
                    if ($request->request->get('password') != '') {
                        $password = $request->request->get('password');
                        $password = $this->passwordHasher->hashPassword($user, $password);
                        $user->setPassword($password);
                    };
                    if ($request->request->get('role') != '') {
                        $role = $request->request->get('role');
                        $user->setRoles([$role]);
                    };
                    if ($request->request->get('department') != '') {
                        $department = $this->departmentRepository->findOneBy(['id' => $request->request->get('department')]);
                        $user->setDepartment($department);
                    };
                    if ($request->request->get('emailAddress') != '') {
                        $emailAddress = $request->request->get('emailAddress');
                        $user->setEmailAddress($emailAddress);
                    };

                    $this->manager->persist($user);
                    $this->manager->flush();

                    return $user;
                }
            }
        }

        return null;
    }

    // This function is responsible for deleting a user account from the database
    public function deleteUser($id)
    {
        $user = $this->userRepository->find($id);
        $this->entityDeletionService->deleteEntity('user', $id);

        $this->manager->remove($user);
        $this->manager->flush();
    }

    // This function is responsible for blocking a user account

    public function blockUser($id)
    {
        $user = $this->userRepository->find($id);
        $user->setBlocked(true);
        $user->setPassword('');
        $user->setRoles(['ROLE_USER']);
        $this->manager->persist($user);
        $this->manager->flush();
    }
}