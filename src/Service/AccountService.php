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

        $this->em                           = $em;

        $this->validator = $validator;

        $this->logger = $logger;
    }





    // This function is responsible for creating a new user account and persisting it to the database
    /**
     * Creates a new user account and persists it to the database.
     *
     * @param Request $request The request object containing the user data.
     *
     * @return User The newly created user.
     *
     * @throws \InvalidArgumentException If the user data is invalid.
     */
    public function createAccount(Request $request): User
    {
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
            throw new \InvalidArgumentException($errorsString);
        }

        $this->em->persist($user);
        $this->em->flush();

        return  $user;
    }






    // This function is responsible for modifying a user account and persisting the modification to the database
    /**
     * Modifies an existing user account with the provided data and persists changes to the database.
     *
     * This method updates user account information based on non-empty values in the request.
     * It tracks which fields were modified and returns a comma-separated string of modified fields.
     *
     * @param Request $request The HTTP request containing the user data to update (username, password, role, department, emailAddress, operator)
     * @param User $user The user entity to be modified
     *
     * @return string A comma-separated list of fields that were modified, or 'rien' (nothing) if no changes were made
     *
     * @throws \InvalidArgumentException If the modified user data fails validation
     * @throws \Exception If any other error occurs during the modification process
     */
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
                throw new \InvalidArgumentException($errorsString);
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



    /**
     * Blocks a user account by setting its blocked status to true, clearing the password,
     * and resetting roles to basic user access.
     *
     * @param User $user The user entity to be blocked
     *
     * @return void
     */
    public function blockUser(User $user)
    {
        $user->setBlocked(true);
        $user->setPassword('');
        $user->setRoles(['ROLE_USER']);
        $this->em->persist($user);
        $this->em->flush();
    }


    /**
     * Unblocks a user account by setting its blocked status to null.
     *
     * This method removes the blocked status from a user account,
     * allowing the user to regain access to the system.
     *
     * @param User $user The user entity to be unblocked
     *
     * @return void
     */
    public function unblockUser(User $user)
    {
        $user->setBlocked(null);
        $this->em->persist($user);
        $this->em->flush();
    }


    /**
     * Updates a user's email address based on their username.
     *
     * This method generates an email address by appending the domain 'plasticomnium.com'
     * to the user's username and updates the user's email address field.
     *
     * @param User $user The user entity whose email address will be updated
     *
     * @return void
     */
    public function updateUserEmail(User $user)
    {
        $username = $user->getUsername();
        $emailAddress = $username . '@' . 'plasticomnium.com';
        $user->setEmailAddress($emailAddress);
        $this->em->persist($user);
        $this->em->flush();
    }


    /**
     * Transfers all work items from one user to another.
     *
     * This method transfers ownership of various entities (incidents, uploads, zones,
     * product lines, categories, and buttons) from the original user to the recipient user.
     * It also checks if there are any approbations that cannot be transferred.
     *
     * @param User $originalUser The user from whom work items will be transferred
     * @param User $recipient The user who will receive the work items
     *
     * @return void
     *
     * @throws \InvalidArgumentException If there are approbations that cannot be transferred
     */
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

    /**
     * Transfers entities from one user to another using dynamic getter and setter methods.
     *
     * This method retrieves entities associated with the original user using the specified getter method,
     * then reassigns each entity to the recipient user using the specified setter method.
     * Each modified entity is then persisted to the entity manager.
     *
     * @param User $originalUser The user from whom entities will be transferred
     * @param User $recipient The user who will receive the entities
     * @param string $getterMethod The name of the method used to retrieve entities from the original user
     * @param string $setterMethod The name of the method used to set the new user on each entity
     *
     * @return void
     */
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

    /**
     * Checks if a user has any approbations that cannot be transferred.
     *
     * This method examines all approbations associated with the original user
     * and throws an exception if any are found, as approbations must be re-validated
     * rather than transferred. The exception message includes the names of all
     * documents that need re-validation.
     *
     * @param User $originalUser The user whose approbations are being checked
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the user has approbations that cannot be transferred
     */
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
                throw new \InvalidArgumentException('Les approbations ne peuvent pas être transférées; veuillez revalider les documents suivants: ' . $namesString);
            }
        }
    }
}
