<?php

namespace App\Service;

use App\Entity\User;

use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

use App\Service\EntityDeletionService;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// This class is responsible for managing the user accounts logic
class AccountService extends AbstractController
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
                    $errorMessages[] = $violation->getMessage();
                }

                $errorsString = implode("\n", $errorMessages);

                throw new \Exception($errorsString);
            }

            $this->manager->persist($user);
            $this->manager->flush();

            return  $user;
        }
    }






    // This function is responsible for modifying a user account and persisting the modification to the database
    public function modifyAccount(Request $request, User $user)
    {
        $modified = '';
        try {
            if ($request->request->get('username') != '') {
                $user->setUsername($request->request->get('username'));
                $modified .= 'Nom d\'utilisateur, ';
            };
            if ($request->request->get('password') != '') {
                $password = $this->passwordHasher->hashPassword($user, $request->request->get('password'));
                $user->setPassword($password);
                $modified .= 'Mot de passe, ';
            };
            if ($request->request->get('role') != '') {
                $user->setRoles([$request->request->get('role')]);
                $modified .= 'Rôle, ';
            };
            if ($request->request->get('department') != '') {
                $department = $this->departmentRepository->findOneBy(['id' => $request->request->get('department')]);
                $user->setDepartment($department);
                $modified .= 'Service, ';
            };
            if ($request->request->get('emailAddress') != '') {
                $user->setEmailAddress($request->request->get('emailAddress'));
                $modified .= 'Adresse email, ';
            };
            $errorsString = '';
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $violation) {
                    $errorMessages[] = $violation->getMessage();
                }
                $errorsString = implode("\n", $errorMessages);
                throw new \Exception($errorsString);
            }
            if ($modified === '' || $modified === null || $errorsString !== '') {
                $modified = 'rien';
            }
            $modified = rtrim($modified, ', ');
            $this->manager->persist($user);
            $this->manager->flush();

            return $modified;
        } catch (\Exception $e) {
            $this->logger->error('account modification error', [
                ' modified account' => $user,
                'modifier' => $this->getUser(),
                'error' => $e->getMessage()
            ]);
            throw $e;  // Re-throw the exception so it propagates to the controller
        } finally {
            $this->logger->info('account modification attempt', [
                'modified account' => $user,
                'modifier' => $this->getUser()
            ]);
        }
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
