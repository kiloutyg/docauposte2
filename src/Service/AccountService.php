<?php

namespace App\Service;

use App\Entity\User;

use App\Repository\DepartmentRepository;
use App\Repository\OperatorRepository;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


// This class is responsible for managing the user accounts logic
class AccountService extends AbstractController
{
    private $departmentRepository;
    private $operatorRepository;

    private $passwordHasher;

    private $em;

    private $validator;

    private $logger;

    public function __construct(
        DepartmentRepository $departmentRepository,
        OperatorRepository $operatorRepository,

        UserPasswordHasherInterface $passwordHasher,

        EntityManagerInterface $em,

        ValidatorInterface $validator,

        LoggerInterface $logger

    ) {
        $this->departmentRepository = $departmentRepository;
        $this->operatorRepository = $operatorRepository;

        $this->passwordHasher = $passwordHasher;

        $this->em = $em;

        $this->validator = $validator;

        $this->logger = $logger;
    }





    // This function is responsible for creating a new user account and persisting it to the database
    public function createAccount(Request $request)
    {
        // create the user
        $user = new User();
        $password = $request->request->get('password');
        $password = $this->passwordHasher->hashPassword($user, $password);
        $user->setUsername($request->request->get('username'));
        $user->setPassword($password);
        $user->setRoles([$request->request->get('role')]);
        $user->setDepartment($this->departmentRepository->find($request->request->get('department')));
        $user->setEmailAddress($request->request->get('emailAddress'));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            $errorsString = implode("\n", $errorMessages);
            throw new \Exception($errorsString);
        }

        $this->em->persist($user);
        $this->em->flush();

        return  $user;
    }






    // This function is responsible for modifying a user account and persisting the modification to the database
    public function modifyAccount(Request $request, User $user)
    {
        $modified = '';
        try {
            if ($request->request->get('username') != '') {
                $this->logger->debug('$request->request->get(\'username\')', [$request->request->get('username')]);
                $user->setUsername($request->request->get('username'));
                $modified .= 'Nom d\'utilisateur, ';
            }
            if ($request->request->get('password') != '') {
                $password = $this->passwordHasher->hashPassword($user, $request->request->get('password'));
                $user->setPassword($password);
                $modified .= 'Mot de passe, ';
            }
            if ($request->request->get('role') != '') {
                $this->logger->debug('$request->request->get(\'role\')', [$request->request->get('role')]);
                $user->setRoles([$request->request->get('role')]);
                $modified .= 'Rôle, ';
            }
            if ($request->request->get('department') != '') {
                $department = $this->departmentRepository->find((int)$request->request->get('department'));
                $this->logger->debug('department $request->request->get', [$department]);
                $user->setDepartment($department);
                $modified .= 'Service, ';
            }
            if ($request->request->get('emailAddress') != '') {
                $this->logger->debug('$request->request->get(\'emailAddress\')', [$request->request->get('emailAddress')]);
                $user->setEmailAddress($request->request->get('emailAddress'));
                $modified .= 'Adresse email, ';
            }
            if ($request->request->get('operator') != '') {
                $this->logger->debug('$request->request->get(\'operator\')', [$request->request->get('operator')]);
                $operator = $this->operatorRepository->find($request->request->get('operator'));
                $this->logger->debug('operator', [$operator]);
                $user->setOperator($operator);
                $modified .= 'Identité d\'opérateur liée, ';
            }

            $errorsString = '';
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $violation) {
                    $errorMessages[] = $violation->getMessage();
                }
                $errorsString = implode("\n", $errorMessages);
                $this->logger->error('Validation errors while modifying account', [$errorsString]);
                throw new \Exception($errorsString);
            }
            if ($modified === '' || $modified === null || $errorsString !== '') {
                $modified = 'rien';
            }
            $modified = rtrim($modified, ', ');
            $this->em->persist($user);
            $this->em->flush();

            return $modified;
        } catch (\Exception $e) {
            $this->logger->error('Account modification error', [
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



    public function blockUser(User $user)
    {
        $user->setBlocked(true);
        $user->setPassword('');
        $user->setRoles(['ROLE_USER']);
        $this->em->persist($user);
        $this->em->flush();
    }


    public function unblockUser(User $user)
    {
        $user->setBlocked(null);
        $this->em->persist($user);
        $this->em->flush();
    }


    public function updateUserEmail(User $user)
    {
        $username = $user->getUsername();
        $emailAddress = $username . '@' . 'plasticomnium.com';
        $user->setEmailAddress($emailAddress);
        $this->em->persist($user);
        $this->em->flush();
    }


    public function transferWork(User $originalUser, User $recipient)
    {
        $this->transferEntities($originalUser, $recipient, 'getIncidents', 'setUploader');
        $this->transferEntities($originalUser, $recipient, 'getOldUploads', 'setOldUploader');
        $this->transferEntities($originalUser, $recipient, 'getUploads', 'setUploader');
        $this->transferEntities($originalUser, $recipient, 'getZones', 'setCreator');
        $this->transferEntities($originalUser, $recipient, 'getProductLines', 'setCreator');
        $this->transferEntities($originalUser, $recipient, 'getCategories', 'setCreator');
        $this->transferEntities($originalUser, $recipient, 'getButtons', 'setCreator');
        $this->checkApprobations($originalUser);

        $this->em->flush();
    }

    private function transferEntities(User $originalUser, User $recipient, string $getterMethod, string $setterMethod): void
    {
        $entities = $originalUser->$getterMethod();
        if ($entities !== null && is_iterable($entities)) {
            foreach ($entities as $entity) {
                $entity->$setterMethod($recipient);
                $this->em->persist($entity);
            }
        }
    }

    private function checkApprobations(User $originalUser): void
    {
        $approbations = $originalUser->getApprobations();
        if ($approbations !== null && is_iterable($approbations)) {
            $uploadsNames = [];
            foreach ($approbations as $approbation) {
                $uploadsNames[] = $approbation->getValidation()->getUpload()->getButton()->getName();
            }
            if (!empty($uploadsNames)) {
                $namesString = implode(', ', $uploadsNames);
                throw new \Exception('Les approbations ne peuvent pas être transférées; veuillez revalider les documents suivants: ' . $namesString);
            }
        }
    }
}
