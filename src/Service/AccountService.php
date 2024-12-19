<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

use App\Service\EntityDeletionService;

// This class is responsible for managing the user accounts logic
class AccountService
{

    private $userRepository;
    private $departmentRepository;

    private $entityDeletionService;

    private $passwordHasher;

    private $manager;

    private $validator;

    private $logger;

    public function __construct(

        UserRepository $userRepository,
        DepartmentRepository $departmentRepository,

        EntityDeletionService $entityDeletionService,

        UserPasswordHasherInterface $passwordHasher,

        EntityManagerInterface $manager,

        ValidatorInterface $validator,

        LoggerInterface $logger

    ) {
        $this->userRepository = $userRepository;
        $this->departmentRepository = $departmentRepository;

        $this->entityDeletionService = $entityDeletionService;

        $this->passwordHasher = $passwordHasher;

        $this->manager = $manager;

        $this->validator = $validator;

        $this->logger = $logger;
    }





    // This function is responsible for creating a new user account and persisting it to the database
    public function createAccount(Request $request)
    {
        $name = $request->request->get('username');
        $password = $request->request->get('password');
        $role = $request->request->get('role');
        $departmentId = $request->request->get('department');
        $department = $this->departmentRepository->findOneBy(['id' => $departmentId]);
        $emailAddress = $request->request->get('emailAddress');

        // check if the username is already in use
        $user = $this->userRepository->findOneBy(['username' => $name]);
        if ($user) {
            throw new \Exception('Ce nom d\'utilisateur est déja utilisé.');
        } else {
            // create the user
            $user = new User();

            $password = $this->passwordHasher->hashPassword($user, $password);
            $user->setUsername($name);
            $user->setPassword($password);
            $user->setRoles([$role]);
            $user->setDepartment($department);
            $user->setEmailAddress($emailAddress);

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $violation) {
                    // You can use ->getPropertyPath() if you need to show the field name
                    // $errorMessages[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
                    $errorMessages[] = $violation->getMessage();
                }

                // Now you have an array of user-friendly messages you can display
                // For example, you can separate them with new lines when displaying in text format:
                $errorsString = implode("\n", $errorMessages);

                // If you need to return JSON response:
                // return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);

                throw new \Exception($errorsString);
            }

            $this->manager->persist($user);
            $this->manager->flush();

            return  $user;
        }

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

    public function unblockUser($id)
    {
        $user = $this->userRepository->find($id);
        $user->setBlocked(null);
        $this->manager->persist($user);
        $this->manager->flush();
    }


    public function updateUserEmail(User $user)
    {
        $username = $user->getUsername();
        $emailAddress = $username . '@' . 'plasticomnium.com';
        $user->setEmailAddress($emailAddress);
        $this->manager->persist($user);
        $this->manager->flush();
        return;
    }

    public function transferWork(Request $request, int $userId)
    {
        $user = $this->userRepository->find($userId);
        $recipient = $this->userRepository->find($request->request->get('work-transfer-recipient'));

        if ($user->getIncidents()) {
            foreach ($user->getIncidents() as $incident) {
                $incident->setUploader($recipient);
                $this->manager->persist($incident);
            }
        }

        if ($user->getOldUploads()) {
            foreach ($user->getOldUploads() as $oldUpload) {
                $oldUpload->setOldUploader($recipient);
                $this->manager->persist($oldUpload);
            }
        }


        if ($user->getUploads()) {
            foreach ($user->getUploads() as $upload) {
                $upload->setUploader($recipient);
                $this->manager->persist($upload);
            }
        }

        if ($user->getZones()) {
            foreach ($user->getZones() as $zone) {
                $zone->setCreator($recipient);
                $this->manager->persist($zone);
            }
        }

        if ($user->getProductLines()) {
            foreach ($user->getProductLines() as $productLine) {
                $productLine->setCreator($recipient);
                $this->manager->persist($productLine);
            }
        }

        if ($user->getCategories()) {
            foreach ($user->getCategories() as $category) {
                $category->setCreator($recipient);
                $this->manager->persist($category);
            }
        }

        if ($user->getButtons()) {
            foreach ($user->getButtons() as $button) {
                $button->setCreator($recipient);
                $this->manager->persist($button);
            }
        }

        if ($user->getApprobations()) {
            $uploadsNames = [];
            foreach ($user->getApprobations() as $approbation) {
                $uploadsNames[] = $approbation->getValidation()->getUpload()->getButton()->getName();
            }
            if (!empty($uploadsNames)) {
                $namesString = implode(', ', $uploadsNames); // Convert the names array to a comma-separated string
                throw new \Exception('Les approbations ne peuvent pas être transférées; veuillez revalider les documents suivants: ' . $namesString);
            }
        }

        $this->manager->flush();

        return;
    }
}
