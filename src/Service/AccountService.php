<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

// This class is responsible for managing the user accounts logic
class AccountService
{
    private $passwordHasher;
    private $userRepository;
    private $manager;
    private $departmentRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $manager,
        DepartmentRepository $departmentRepository
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->manager = $manager;
        $this->departmentRepository = $departmentRepository;
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
    public function modifyAccount(Request $request, UserInterface $currentUser)
    {
        if ($request->getMethod() == 'POST') {
            $name = $request->request->get('current_username'); // Change 'username' to 'current_username'
            $newName = $request->request->get('username');
            $password = $request->request->get('password');
            $role = $request->request->get('role');
            $departmentId = $request->request->get('department');
            $department = $this->departmentRepository->findOneBy(['id' => $departmentId]);
            $emailAddress = $request->request->get('emailAddress');

            // Check if the username is already in use
            $user = $this->userRepository->findOneBy(['username' => $name]); // Look up the user by the current_username

            // Check if the current user is a super admin
            if (in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())) {

                // Get the user to be modified
                $user = $this->userRepository->findOneBy(['username' => $name]);
                if ($user) {
                    // Update the user details
                    $password = $this->passwordHasher->hashPassword($user, $password);
                    $user->setUsername($newName); // Set the new username
                    $user->setPassword($password);
                    $user->setRoles([$role]);
                    $user->setDepartment($department);
                    $user->setEmailAddress($emailAddress);
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
        $this->manager->remove($user);
        $this->manager->flush();
    }
}