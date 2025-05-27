<?php

namespace App\Service;

use App\Entity\Operator;
use App\Entity\Trainer;

use App\Service\Facade\TrainingManagerFacade;
use App\Service\Facade\EntityManagerFacade;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Service class for managing Operator entities and related business logic.
 *
 * Handles operator lifecycle (creation, update, inactivation, deletion),
 * form processing, search, and initialization of teams and UAPs.
 *
 * @package App\Service
 */
class OperatorService extends AbstractController
{
    private     $logger;
    private     $projectDir;

    private     $validator;
    private    $entityManagerFacade;
    private    $trainingManagerFacade;

    public function __construct(
        LoggerInterface                 $logger,
        ParameterBagInterface           $params,
        ValidatorInterface              $validator,
        EntityManagerFacade             $entityManagerFacade,
        TrainingManagerFacade           $trainingManagerFacade
    ) {
        $this->logger                   = $logger;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->validator                = $validator;

        $this->entityManagerFacade      = $entityManagerFacade;
        $this->trainingManagerFacade    = $trainingManagerFacade;
    }


    /**
     * Checks for operators to set as inactive, mark for deletion, or delete, based on training activity.
     * Ensures the process runs only once per day.
     *
     * @return array|null Returns an array with counts of deactivated and deleted operators, or null if already checked today.
     */
    public function operatorCheckForAutoDelete()
    {
        $today = new \DateTime();
        $fileName = 'checked_for_unactive_operator.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;

        if (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false) {

            $this->setOperatorToInactive($filePath, $today);

            $this->setOperatorToBeDeleted($filePath, $today);

            $toBeDeletedOperatorsIds = $this->deleteToBeDeletedOperator($filePath, $today);

            return [
                'findDeactivatedOperators' => count($this->entityManagerFacade->findDeactivatedOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
        }
    }


    /**
     * Sets operators with no recent training activity to inactive status.
     *
     * Identifies operators who haven't had training recently and marks them as inactive
     * by setting their inactive date to the current date. Updates the check file
     * to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used to mark operators as inactive
     *
     * @return void
     */
    private function setOperatorToInactive(string $filePath, \DateTime $today)
    {
        $inActiveOperators = $this->entityManagerFacade->findOperatorWithNoRecentTraining();
        if (count($inActiveOperators) > 0) {
            foreach ($inActiveOperators as $operator) {
                if ($operator->isIsTrainer() && $this->trainingManagerFacade->trainerInactivityCheck($operator)) {
                    // Skip trainer if they have recently had training, if both conditions are true,
                    continue;
                }
                $operator->setInactiveSince($today);
                $this->entityManagerFacade->getEntityManager()->persist($operator);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }


    /**
     * Marks inactive operators for deletion.
     *
     * Identifies operators who are already inactive and marks them for deletion
     * by setting their tobedeleted date to the current date. Updates the check file
     * to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used to mark operators for deletion
     *
     * @return void
     */
    private function setOperatorToBeDeleted(string $filePath, \DateTime $today)
    {
        $operatorSetToBeDeleted = $this->entityManagerFacade->findInActiveOperators();
        if (count($operatorSetToBeDeleted) > 0) {
            foreach ($operatorSetToBeDeleted as $operator) {
                if ($operator->isIsTrainer() && $this->trainingManagerFacade->trainerInactivityCheck($operator)) {
                    // Skip trainer if they have recently had training, if both conditions are true,
                    continue;
                }
                $operator->setTobedeleted($today);
                $this->entityManagerFacade->getEntityManager()->persist($operator);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
    }



    /**
     * Permanently deletes operators that were previously marked for deletion.
     *
     * Retrieves all operators marked for deletion, removes them from the database,
     * and updates the check file to record that this process was run today.
     *
     * @param string    $filePath   Path to the file that tracks when this check was last performed
     * @param \DateTime $today      Current date used for updating the check file

     *
     * @return array    Array of IDs of the operators that were deleted
     */
    private function deleteToBeDeletedOperator(string $filePath, \DateTime $today)
    {
        $toBeDeletedOperatorsIds = $this->entityManagerFacade->findOperatorToBeDeleted();
        if (count($toBeDeletedOperatorsIds) > 0) {
            foreach ($toBeDeletedOperatorsIds as $operatorId) {
                $this->entityManagerFacade->deleteEntity('operator', $operatorId);
            }
            $this->entityManagerFacade->getEntityManager()->flush();
            file_put_contents($filePath, $today->format('Y-m-d'));
        }
        return $toBeDeletedOperatorsIds;
    }


    /**
     * Forcefully deletes all operators that are marked for deletion, bypassing any delay restrictions.
     *
     * Retrieves all operators marked for deletion without considering any time constraints,
     * and permanently removes them from the database. Skips trainers who have recent training activity.
     *
     * @return int The number of operators that were deleted
     */
    public function forceDeleteToBeDeletedOperator(): int
    {
        $toBeDeletedOperators = $this->entityManagerFacade->findOperatorToBeDeletedWithNoDelayRestriction();
        $numberOfToBeDeleted = count($toBeDeletedOperators);
        $this->logger->info('forceDeleteToBeDeletedOperator ' . $numberOfToBeDeleted . ' operators marked for deletion');

        $numberOfNonToBeDeleted = 0;
        if ($numberOfToBeDeleted > 0) {
            foreach ($toBeDeletedOperators as $operator) {
                if ($operator->isIsTrainer() && $this->trainingManagerFacade->trainerInactivityCheck($operator)) {
                    $numberOfNonToBeDeleted++;
                    continue;
                }
                $this->entityManagerFacade->deleteEntity('operator', $operator->getID());
            }
            $this->entityManagerFacade->getEntityManager()->flush();
        }
        $this->logger->info('forceDeleteToBeDeletedOperator ' . $numberOfNonToBeDeleted . ' operators unmarked for deletion');

        return $numberOfToBeDeleted - $numberOfNonToBeDeleted;
    }


    /**
     * Handles updates to an operator from a form submission, including trainer status and UAP assignments.
     * Reactivates the operator if needed.
     *
     * @param Form     $form     The submitted form.
     * @param Operator $operator The operator entity to update.
     * @return bool Always returns true if successful.
     */
    public function editOperatorService(Form $form, Operator $operator)
    {
        $this->trainingManagerFacade->handleTrainerStatus($form->get('isTrainer')->getData(), $operator);
        $this->reactivateOperatorIfNeeded($operator);
        $this->trainingManagerFacade->updateOperatorUaps($form->get('uaps')->getData()->toArray(), $operator);

        $this->entityManagerFacade->getEntityManager()->flush();

        return true;
    }




    /**
     * Checks if the operator name in the request matches the expected format (firstname.surname, lowercased).
     *
     * @param Request $request The HTTP request containing operator data.
     * @return bool True if the name matches, false otherwise.
     */
    private function reactivateOperatorIfNeeded(Operator $operator): void
    {
        if ($operator->getTobedeleted() === null) {
            return;
        }

        $operator->setTobedeleted(null);
        $operator->setLasttraining(new \DateTime());
        $operator->setInactiveSince(null);
    }



    /**
     * Validates if the operator name in the request follows the expected format.
     *
     * Checks if the operator name provided in the request matches the expected format
     * of "firstname.surname" in lowercase, by comparing it with the separately provided
     * firstname and surname fields.
     *
     * @param Request $request The HTTP request containing operator data fields:
     *                         'newOperatorFirstname', 'newOperatorSurname', and 'newOperatorName'
     * @return bool True if the name format is valid (matches firstname.surname in lowercase),
     *              false otherwise
     */
    public function autoOperatorNameCheckerFromRequest(Request $request): bool
    {

        $surname = $request->request->get('newOperatorSurname');
        $firstname = $request->request->get('newOperatorFirstname');
        $concatenedOperatorNameNotLower = $firstname . '.' . $surname;
        $concatenedOperatorNameLower = strtolower($concatenedOperatorNameNotLower);
        $operatorName = $request->request->get('newOperatorName');

        return $concatenedOperatorNameLower === $operatorName;
    }



    /**
     * Processes the creation or update of an operator based on request data.
     * Handles duplicate detection, team/UAP assignment, and validation.
     *
     * @param string $operatorName The operator's name.
     * @param int    $operatorCode The operator's code.
     * @param int    $teamId       The team ID.
     * @param int    $uapId        The UAP ID.
     * @return void
     */
    public function processOperatorFromRequest(string $operatorName, int $operatorCode, int $teamId, int $uapId)
    {

        $team = $this->entityManagerFacade->find('team', $teamId);
        $uap = $this->entityManagerFacade->find('uap', $uapId);
        $em = $this->entityManagerFacade->getEntityManager();

        $existingOperator = $this->entityManagerFacade->findOneBy('operator', ['name' => $operatorName]);
        if ($existingOperator == null) {
            $existingOperator = $this->entityManagerFacade->findOneBy('operator', ['code' => $operatorCode]);
        }

        if ($existingOperator != null) {

            if ($existingOperator->getTeam() == $team && $existingOperator->getUaps()->contains($uap)) {
                $this->addFlash('danger', 'Cet opérateur existe déjà dans cette equipe et uap');
                return;
            } else {
                $existingOperator->setTeam($team);
                $existingOperator->addUap($uap);
                $em->persist($existingOperator);
                $em->flush();
                $this->addFlash('success', 'L\'opérateur a bien été ajouté et son equipe et son UAP ont été modifiées');
                return;
            }
        }

        $operator = new Operator();
        $operator->setName($operatorName);
        $operator->setTeam($team);
        $operator->addUap($uap);
        $operator->setCode($operatorCode);

        $errors = $this->validator->validate($operator);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                // You can use ->getPropertyPath() if you need to show the field name
                $errorMessages[] = $violation->getMessage();
            }

            // Now you have an array of user-friendly messages you can display
            // For example, you can separate them with new lines when displaying in text format:
            $errorsString = implode("\n", $errorMessages);
            $this->logger->error('danger', [$errorsString]);
            return;
        }

        $em->persist($operator);
        $em->flush();

        $this->addFlash('success', 'L\'opérateur a bien été ajouté');
    }


    /**
     * Handles the creation of a new operator from a form, including trainer assignment and UAP relationships.
     *
     * @param Operator $newOperator The new operator entity.
     * @param Form     $form        The submitted form.
     * @return int The ID of the created operator.
     */
    public function processNewOperatorFromFormType(Operator $newOperator, Form $form)
    {
        $trainerBool = $form->get('isTrainer')->getData();
        $em = $this->entityManagerFacade->getEntityManager();
        if ($trainerBool) {
            $trainer = new Trainer();
            $trainer->setOperator($newOperator);
            $trainer->setDemoted(false);
            $em->persist($trainer);
            $newOperator->setTrainer($trainer);
        } elseif (!$trainerBool) {
            $trainer = $newOperator->getTrainer();
            $newOperator->setTrainer(null);
            if ($trainer != null) {
                $em->remove($trainer);
            }
        }
        $operator = $form->getData();
        $uaps = $operator->getUaps();
        foreach ($uaps as $uap) {
            $uap->addOperator($operator);
            $em->persist($uap);
        }
        $em->persist($operator);
        $em->flush();

        return $operator->getId();
    }



    /**
     * Finds an entity by name or returns a default if not found.
     *
     * @param array  $entities    Array of entities to search.
     * @param string $name        The name to search for.
     * @param string $defaultName The default name to return if not found.
     * @return object The found entity.
     * @throws \InvalidArgumentException If the default entity is not found.
     */
    public function findEntityByName(array $entities, string $name, string $defaultName)
    {
        foreach ($entities as $entity) {
            if ($entity->getName() === $name) {
                return $entity;
            }
        }
        foreach ($entities as $entity) {
            if ($entity->getName() === $defaultName) {
                return $entity;
            }
        }
        throw new \InvalidArgumentException('Default entity not found');
    }




    /**
     * Performs a search for operators based on request parameters (supports both JSON and form data).
     *
     * @param Request $request The HTTP request containing search parameters.
     * @return array Search results.
     */
    public function operatorEntitySearch(Request $request): array
    {
        if ($request->getContentTypeFormat() == 'json') {
            $data = json_decode($request->getContent(), true);
            $name       = $data['search_name'];
            $code       = $data['search_code'];
            $team       = $data['search_team'];
            $uap        = $data['search_uap'];
            $trainer    = $data['search_trainer'];
        } else {
            $name       = $request->request->get('search_name');
            $code       = $request->request->get('search_code');
            $team       = $request->request->get('search_team');
            $uap        = $request->request->get('search_uap');
            $trainer    = $request->request->get('search_trainer');
        }
        return $this->entityManagerFacade->findBySearchQuery($name, $code, $team, $uap, $trainer);
    }



    /**
     * Handles the creation of teams and UAPs from form submissions, including validation and error handling.
     *
     * @param Form    $uapForm  The UAP form.
     * @param Form    $teamForm The team form.
     * @param Request $request  The HTTP request.
     * @return void
     */
    public function operatorTeamUapFormManagement(Form $uapForm, Form $teamForm, Request $request): void
    {
        $em = $this->entityManagerFacade->getEntityManager();
        $teamForm->handleRequest($request);
        $uapForm->handleRequest($request);
        if ($teamForm->isSubmitted()) {
            if ($teamForm->isValid()) {
                $team = $teamForm->getData();
                $em->persist($team);
                $em->flush();
                $this->addFlash('success', 'team has been created');
            } else {
                // Validation failed, get the error message and display it
                $errorMessageTeam = $teamForm->getErrors(true)->current()->getMessage();
                $this->addFlash('danger', $errorMessageTeam);
                $this->logger->error('Error while creating team', [$errorMessageTeam]);
            }
        }
        if ($uapForm->isSubmitted()) {
            if ($uapForm->isValid()) {
                $uap = $uapForm->getData();
                $em->persist($uap);
                $em->flush();
                $this->addFlash('success', 'Uap has been created');
            } else {
                // Validation failed, get the error message and display it
                $errorMessageUap = $uapForm->getErrors(true)->current()->getMessage();
                $this->addFlash('danger', $errorMessageUap);
                $this->logger->error('Error while creating UAP', [$errorMessageUap]);
            }
        }
    }



    /**
     * Ensures that at least one team and one UAP exist in the system, initializing them if necessary.
     *
     * @return void
     */
    public function teamUapInitialization(): void
    {
        if (count($this->entityManagerFacade->getTeams()) == 0 || $this->entityManagerFacade->findOneBy('team', ['name' => 'INDEFINI']) == null) {
            $this->trainingManagerFacade->teamInitialization();
        }
        if (count($this->entityManagerFacade->getUaps()) == 0 || $this->entityManagerFacade->findOneBy('uap', ['name' => 'INDEFINI']) == null) {
            $this->trainingManagerFacade->uapInitialization();
        }
    }



    /**
     * Deletes an entity (typically an operator) and redirects to the referring page,
     * displaying a success or error message.
     *
     * @param string       $entityType The type of entity to delete.
     * @param int          $entityId   The ID of the entity to delete.
     * @param Request|null $request    The HTTP request (optional, for redirect).
     * @return Response The HTTP response (redirect).
     */
    public function deleteActionOperatorService(string $entityType, int $entityId, ?Request $request = null): Response

    {
        $result = $this->entityManagerFacade->deleteEntity($entityType, $entityId);

        if (!$result) {
            $this->addFlash('danger',  $entityType . ' n\'a pas pu être supprimé');
        } else {
            $this->addFlash('success', $entityType . ' a bien été supprimé');
        }

        // Check if this is a Turbo frame request
        if ($request && $request->headers->get('Turbo-Frame')) {
            // For Turbo frame requests, redirect to the edit route with a dummy operator ID
            // This will trigger the editOperatorAction which renders the correct template
            return $this->redirectToRoute('app_operator_edit', ['operator' => $entityId]);
        } else {
            // For regular requests, redirect to the referring page
            $originUrl = $request->headers->get('referer') ?? 'app_base';
            return $this->redirect($originUrl);
        }
    }
}
