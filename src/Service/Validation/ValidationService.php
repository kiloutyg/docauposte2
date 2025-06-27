<?php

namespace App\Service\Validation;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Entity\Upload;
use App\Entity\User;
use App\Entity\Department;
use App\Entity\Validation;
use App\Entity\Approbation;

use App\Service\Factory\RepositoryFactory;
use App\Service\Factory\ServiceFactory;

use Doctrine\Common\Collections\Collection;

class ValidationService extends AbstractController
{
    private   $logger;
    private   $em;
    private   $projectDir;
    private   $params;

    private   $repositoryFactory;
    private   $serviceFactory;
    private   $userRepository;
    private   $validationRepository;
    private   $approbationRepository;

    private   $mailerService;

    private   $trainingRecordService;


    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,
        ParameterBagInterface           $params,

        RepositoryFactory               $repositoryFactory,
        ServiceFactory                  $serviceFactory
    ) {
        $this->logger                   = $logger;
        $this->em                       = $em;
        $this->projectDir               = $params->get(name: 'kernel.project_dir');
        $this->params                   = $params;

        $this->repositoryFactory        = $repositoryFactory;
        $this->serviceFactory           = $serviceFactory;

        $this->userRepository           = $this->repositoryFactory->getRepository(entityType: 'user');
        $this->validationRepository     = $this->repositoryFactory->getRepository(entityType: 'validation');
        $this->approbationRepository    = $this->repositoryFactory->getRepository(entityType: 'approbation');

        $this->mailerService            = $this->serviceFactory->getService(className: 'mailer');
        $this->trainingRecordService    = $this->serviceFactory->getService(className: 'operator\\trainingRecord');
    }




    /**
     * Creates a new validation process for an uploaded document.
     *
     * This method creates a validation record for an upload, processes any comments
     * provided in the request, and creates approbation processes for each selected
     * validator. It also sends notification emails to the validators.
     *
     * @param Upload $upload The upload entity for which to create a validation
     * @param Request $request The HTTP request containing validation data (validator selections and comments)
     *
     * @return Validation The newly created and persisted Validation entity
     */
    public function createValidation(Upload $upload, Request $request): void
    {

        $this->logger->info('ValidationService::createValidation : request: ', [$request->request->all()]);
        // Create empty arrays to store values for validator_department and validator_user
        $validator_user_values = [];

        // Iterate through the keys in the request
        foreach ($request->request->keys() as $key) {
            // If the key contains 'validator_user', add its value to the validator_user_values array
            if (strpos($key, 'validator_user') !== false && $request->request->get($key) !== null && $request->request->get($key) !== '') {
                $validator_user_values[] = $request->request->get($key);
            }
        }

        // Create a new Validation instance
        $validation = new Validation();

        // Set the Upload object on the Validation instance
        $validation->setUpload($upload);

        // Set the status of the Validation instance to false
        $validation->setStatus(null);

        if ($request->request->get('validationComment') !== null) {
            // Store the comment in a variable
            $comment = $request->request->get('validationComment');
            // If the user added a comment persist the comment
            $validation->setComment($comment);
        }
        if ($request->request->get('modificationComment') !== null) {
            // Store the comment in a variable
            $comment = $request->request->get('modificationComment');
            // If the user added a comment persist the comment
            $validation->setComment($comment);
        }
        // Persist the Validation instance to the database
        $this->em->persist($validation);

        // Flush changes to the database
        $this->em->flush();

        // Initialize variables for validator_user and validator_department
        $validator_user = null;

        // Loop through each validator_user value
        foreach ($validator_user_values as $validator_user_value) {
            // If the validator_user value is not null
            if ($validator_user_value !== null || $validator_user_value !== '') {
                // Find the User entity using the repository and the validator_user value
                $validator_user = $this->userRepository->find($validator_user_value);
            }
            // Call the createApprobationProcess method to create an Approbation process
            $this->createApprobationProcess(
                $validation,
                $validator_user
            );
            $validator_user = null;
        }

        // Send a notification email to the validator
        $this->mailerService->approbationEmail($validation);

        $minorModification = $request->request->get(key: 'modification-outlined') === 'minor-modification';

        if (!$minorModification && $request->request->get('display-needed') === 'true' && $request->request->get('training-needed') === 'true') {
            $this->trainingRecordService->updateTrainingRecord($upload);
        }
    }






    /**
     * Updates an existing validation for an upload with new validators and comments.
     *
     * This method updates a validation record associated with an upload. It processes
     * the modification comment, removes existing approbations, creates new approbation
     * processes for selected validators, sends notification emails to the new validators,
     * and updates training records if needed.
     *
     * @param Upload $upload The upload entity whose validation needs to be updated
     * @param Request $request The HTTP request containing validation data (comments, validator selections)
     *
     * @return void This method doesn't return any value
     */
    public function updateValidation(Upload $upload, Request $request)
    {
        $this->logger->info('ValidationService::updateValidation : request: ', [$request->request->all()]);

        // Get the Validation instance associated with the Upload instance
        $validation = $upload->getValidation();

        // Store the comment in a variable
        $comment = $request->request->get('modificationComment');

        $minorModification = $request->request->get(key: 'modification-outlined') === 'minor-modification';

        if ($minorModification) {
            $comment = $comment . ' (modification mineure)';
        }
        // If the user added a comment persist the comment
        if ($comment != null) {
            $validation->setComment($comment);
        }

        // Persist the Validation instance to the database
        $this->em->persist($validation);

        // Flush changes to the database
        $this->em->flush();

        $this->logger->info('ValidationService::updateValidation : Before approbationChangeDetermination');
        $this->approbationChangeDetermination(validation: $validation, request: $request);
        $this->logger->info('ValidationService::updateValidation : After approbationChangeDetermination');

        // Send a notification email to the validator
        $this->mailerService->approbationEmail($validation);
        $this->logger->info('forcedDisplay: ' . $upload->isForcedDisplay() . ' training-needed: ' . $request->request->get('training-needed') . ' display-needed: ' . $request->request->get('display-needed'));
        if (!$minorModification && $request->request->get('display-needed') === 'true' && $request->request->get('training-needed') === 'true') {
            $this->trainingRecordService->updateTrainingRecord($upload);
        }
    }



    /**
     * Determines and processes changes to approbations based on modification type.
     *
     * This method handles the logic for updating approbations when a validation is modified.
     * For minor modifications, it identifies added and removed approbators and updates only
     * those that changed. For major modifications, it removes all existing approbations and
     * creates new ones based on the selected validators in the request.
     *
     * @param Validation $validation The validation entity whose approbations need to be updated
     * @param Request $request The HTTP request containing validator selections and modification type
     *
     * @return void This method doesn't return any value
     */
    private function approbationChangeDetermination(Validation $validation, Request $request)
    {

        $this->logger->info('ValidationService::approbationChangeDetermination : request: ', [$request->request->all()]);
        // Get the approbators associated with the Validation instance
        $approbations = [];
        $approbations = $validation->getApprobations();
        $minorModification = $request->request->get('modification-outlined') == 'minor-modification';

        // Iterate through the keys in the request to get the id for  validator_user
        foreach ($request->request->keys() as $key) {
            // If the key contains 'validator_user', add its value to the validator_user_values array
            if (
                strpos($key, 'validator_user') !== false &&
                $request->request->get($key) !== null &&
                $request->request->get($key) !== ''
            ) {
                $validator_user_values[] = $request->request->get($key);
            }
        }

        if ($minorModification) {
            $this->approbationChangeMinorModification($validation, $request, $approbations);
        } else {
            $this->approbationChangeMajorModification($validation, $approbations, $validator_user_values);
        }
    }




    /**
     * Processes approbation changes for minor modifications to a validation.
     *
     * This method handles the selective updating of approbations when a document undergoes
     * minor modifications. It identifies which approbators were removed and which were added,
     * then removes approbations for removed approbators and creates new approbations for
     * newly added approbators, preserving existing approbations that weren't changed.
     *
     * @param Validation $validation The validation entity whose approbations need to be updated
     * @param Request $request The HTTP request containing the updated validator selections
     * @param Collection $approbations The collection of existing approbations associated with the validation
     *
     * @return void This method doesn't return any value
     */
    private function approbationChangeMinorModification(Validation $validation, Request $request, Collection $approbations)
    {
        $this->logger->info('ValidationService::approbationChangeDetermination: minor modification');
        $diffInApprobators = $this->checkApprobatorChange($request, $validation->getUpload());
        $this->logger->info('ValidationService::approbationChangeDetermination: diffInApprobators: ', $diffInApprobators);
        if (!empty($diffInApprobators)) {
            foreach ($approbations as $approbation) {
                $this->logger->info('ValidationService::approbationChangeDetermination: removing approbators before checks', [$approbation]);
                if (in_array(($approbation->getUserApprobator()->getId()), $diffInApprobators['removedApprobators'])) {
                    $this->logger->info('ValidationService::approbationChangeDetermination: removing approbation after checks', [$approbation]);
                    $this->em->remove($approbation);
                }
            }
            foreach ($diffInApprobators['newApprobators'] as $newApprobatorId) {
                $newApprobatorEntity = $this->userRepository->find($newApprobatorId);
                $this->logger->info('ValidationService::approbationChangeDetermination: creating new approbation', [$newApprobatorEntity]);
                $this->createApprobationProcess(
                    $validation,
                    $newApprobatorEntity
                );
            }
        }
    }




    /**
     * Processes approbation changes for major modifications to a validation.
     *
     * This method handles the complete replacement of approbations when a document undergoes
     * major modifications. It removes all existing approbations associated with the validation
     * and creates new approbation processes for each validator specified in the validator_user_values array.
     *
     * @param Validation $validation The validation entity whose approbations need to be replaced
     * @param Collection $approbations The collection of existing approbations to be removed
     * @param array $validator_user_values Array of user IDs to be set as new validators/approbators
     *
     * @return void This method doesn't return any value
     */
    private function approbationChangeMajorModification(Validation $validation, Collection $approbations, array $validator_user_values)
    {

        $this->logger->info('ValidationService::approbationChangeDetermination: non minor modification');

        foreach ($approbations as $approbation) {
            // Remove the Approbation instance from the database
            $this->em->remove($approbation);
        }

        // Loop through each validator_user value
        foreach ($validator_user_values as $validator_user_value) {
            // If the validator_user value is not null
            if ($validator_user_value !== null || $validator_user_value !== '') {
                // Find the User entity using the repository and the validator_user value
                $validator_user = $this->userRepository->find($validator_user_value);
            }
            // Call the createApprobationProcess method to create an Approbation process
            $this->createApprobationProcess(
                $validation,
                $validator_user
            );
            $validator_user = null;
        }
    }



    /**
     * Creates an approbation process for a user validator.
     *
     * This method creates a new Approbation instance associated with a validation
     * and assigns a user as the approbator. The approbation is then persisted
     * to the database.
     *
     * @param mixed $validation The Validation entity to associate with the approbation
     * @param User|null $validator_user The User entity to set as the approbator,
     *                                 or null if no user is specified
     *
     * @return void This method doesn't return any value
     */
    public function createApprobationProcess(
        $validation,
        ?User $validator_user = null
    ): void {
        // Create a new Approbation instance
        $approbation = new Approbation();
        // Set the Validation object on the Approbation instance
        $approbation->setValidation($validation);
        // Set the UserApprobator object on the Approbation instance
        if ($validator_user !== null || $validator_user !== '') {
            $approbation->setUserApprobator($validator_user);
        }

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();
    }



    /**
     * Identifies changes in approbators between the current request and existing approbations.
     *
     * This method compares the validators selected in the current request with those
     * already associated with an upload's validation. It identifies both newly added
     * validators and removed validators, allowing for precise tracking of approbator changes.
     *
     * @param Request $request The HTTP request containing the form data with validator selections
     * @param Upload $upload The upload entity whose approbators need to be compared
     *
     * @return array An associative array containing two keys:
     *               - 'newApprobators': Array of user IDs that are newly selected as validators
     *               - 'removedApprobators': Array of user IDs that were removed as validators
     */
    public function checkApprobatorChange(Request $request, Upload $upload): array
    {
        $validatorUserValues = [];
        $approbatorUserId = [];

        foreach ($request->request->keys() as $key) {
            // If the key contains 'validator_user', add its value to the validator_user_values array
            if (
                strpos($key, 'validator_user') !== false &&
                $request->request->get($key) !== null &&
                $request->request->get($key) !== ''
            ) {
                $validatorUserValues[] = $request->request->get($key);
            }
        }

        $approbations = $upload->getValidation()->getApprobations();
        foreach ($approbations as $approbation) {
            $approbatorUserId[] = $approbation->getUserApprobator()->getId();
        }

        // Find values in $validatorUserValues that are not in $approbatorUserId
        $newApprobators = array_diff($validatorUserValues, $approbatorUserId);

        // Find values in $approbatorUserId that are not in $validatorUserValues
        $removedApprobators = array_diff($approbatorUserId, $validatorUserValues);

        $this->logger->info(
            'ValidationService::checkApprobatorChange() - Changes in approbators',
            [
                'newApprobators' => $newApprobators,
                'removedApprobators' => $removedApprobators
            ]
        );

        // Return an array with both new and removed approbators for complete change tracking
        return [
            'newApprobators' => $newApprobators,
            'removedApprobators' => $removedApprobators
        ];
    }



    /**
     * Unused method to create an approbation process for a department validator. Might be removed or used in the future.
     * Creates an approbation process for a department validator.
     *
     * This method creates a new Approbation instance associated with a validation
     * and assigns a department as the approbator. The approbation is then persisted
     * to the database.
     *
     * @param mixed $validation The Validation entity to associate with the approbation
     * @param Department|null $validator_department The Department entity to set as the approbator,
     *                                             or null if no department is specified
     *
     * @return Approbation The newly created and persisted Approbation entity
     */
    public function createDepartmentApprobationProcess(
        $validation,
        ?Department $validator_department = null
    ) {
        // Create a new Approbation instance
        $approbation = new Approbation();
        // Set the Validation object on the Approbation instance
        $approbation->setValidation($validation);
        // Set the UserApprobator object on the Approbation instance
        if ($validator_department !== null || $validator_department !== '') {
            $approbation->setDepartmentApprobator($validator_department);
        }

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();

        // Return the Approbation instance
        return $approbation;
    }





    /**
     * Processes an approbation decision for a document validation.
     *
     * This method handles the approval or rejection of a document by a validator.
     * It updates the approbation record with the decision, timestamp, and any comments,
     * then triggers appropriate follow-up actions based on the decision.
     *
     * @param Approbation $approbation The approbation entity to be updated with the approval decision
     * @param Request $request The HTTP request containing approval data ('approvalRadio' and 'approbationComment')
     *
     * @return bool Returns true if the approbation was approved or processing completed successfully,
     *              returns false if the approbation was explicitly rejected
     */
    public function validationApproval(Approbation $approbation, Request $request): bool
    {
        $this->logger->info('ValidationService::validationApproval()');
        // Set response bool value to true
        $response = true;

        // Get the value of the 'approvalRadio' input from the request
        $approvalStr = $request->request->get('approvalRadio');

        // Convert the value to a boolean
        $approval = filter_var($approvalStr, FILTER_VALIDATE_BOOLEAN);

        // Get the value of the 'approbationComment' input from the request
        $comment = $request->request->get('approbationComment');

        // Set the Approval property of the Approbation instance
        $approbation->setApproval($approval);

        // Set the Approval Date property of the Approbation instance
        $approbation->setApprovedAt(new \DateTime());

        // Set the Comment property of the Approbation instance
        $approbation->setComment($comment);

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);
        // Flush changes to the database
        $this->em->flush();

        // Get the Validation object associated with the Approbation instance
        $validation = $approbation->getValidation();

        if ($approbation->isApproval() === false) {
            $response = false;
            $this->mailerService->sendDisapprobationEmail($validation);
        }
        // Call the approbationCheck method to check if all approbations are approved
        $this->approbationCheck($validation);

        $this->logger->info('ValidationService::validationApproval() - response: ', [$response]);

        return $response;
    }






    /**
     * Checks the status of all approbations for a validation and updates the validation status accordingly.
     *
     * This method evaluates all approbations associated with a validation to determine the overall
     * validation status. If any approbation is rejected (false), the validation status is set to false.
     * If all approbations are approved (true), the validation status is set to true. If any approbation
     * is still pending (null), the validation status remains null.
     *
     * @param Validation $validation The validation entity whose approbations need to be checked
     *
     * @return void This method doesn't return any value
     */
    public function approbationCheck(Validation $validation)
    {

        $this->logger->info('ValidationService::approbationCheck() - validationId: ', [$validation]);

        // Get the ID of the Validation instance
        $validationId = $validation->getId();

        // Create an empty array to store Approbation instances

        $status       = null;
        $approbationCount = count($this->approbationRepository->findBy(['Validation' => $validationId]));
        $negApprobations = $this->approbationRepository->findBy(['Validation' => $validationId, 'approval' => false]);
        $posApprobations = $this->approbationRepository->findBy(['Validation' => $validationId, 'approval' => true]);
        $noApprobations  = $this->approbationRepository->findBy(['Validation' => $validationId, 'approval' => null]);

        if ($negApprobations) {
            $this->logger->notice('ValidationService::approbationCheck() - Validation has rejected approbations');
            $status = false;
            $this->updateValidationAndUploadStatus($validation, $status);
        } elseif ($noApprobations) {
            $this->logger->notice('ValidationService::approbationCheck() - Validation has no approbations');
            $status = null;
        } elseif (count($posApprobations) === $approbationCount) {
            $this->logger->notice('ValidationService::approbationCheck() - Validation has all approbations approved');
            $status = true;
            $this->updateValidationAndUploadStatus($validation, $status);
        }
    }





    // This method will also activate the notification email to the uploader
    /**
     * Updates the status of a validation and its associated upload.
     *
     * This method updates the validation status, sets the validation date, and handles
     * the associated upload. If the validation is approved, it also manages any old uploads
     * (deleting files and removing references), sends approval emails, and updates training
     * records if needed.
     *
     * @param Validation $validation The validation entity to update
     * @param bool|null $status The new status to set for the validation (true for approved, false for rejected, null for pending)
     *
     * @return void This method doesn't return any value
     */
    public function updateValidationAndUploadStatus(Validation $validation, ?bool $status)
    {
        $this->logger->info('ValidationService::updateValidationAndUploadStatus: ' . $validation->getId() . ' status: ' . $status);

        if ($validation->isStatus() === false) {
            return;
        }
        // Set the status of the Validation instance to true
        $validation->setStatus($status);
        // Set the Validated Date property of the Validation instance
        $validation->setValidatedAt(new \DateTime());
        // Persist the Validation instance to the database
        $this->em->persist($validation);
        // Flush changes to the database
        $this->em->flush($validation);
        $upload = $validation->getUpload();
        $upload->setValidated($status);
        // Delete the previously retired file if it exists
        if ($upload->getOldUpload() !== null) {
            $oldUpload = $upload->getOldUpload();
        }
        if (isset($oldUpload) && $validation->isStatus() === true) {
            $upload->setOldUpload(null);
        }
        $this->em->persist($upload);
        $this->em->flush($upload);

        if (isset($oldUpload) && $validation->isStatus() === true) {
            $path = $oldUpload->getPath();
            if (file_exists($path)) {
                unlink($path);
            }
            $this->em->remove($oldUpload);
            $this->em->flush($oldUpload);
        }
        $this->logger->info('ValidationService::updateValidationAndUploadStatus - validation->isStatus(): ' . $validation->isStatus() . ' upload->isForcedDisplay(): ' . $upload->isForcedDisplay() . ' upload->isTraining(): ' . $upload->isTraining() . ' $this->trainingRecordService->lastTrainingDateUploadDateComparison($upload): ' . $this->trainingRecordService->lastTrainingDateUploadDateComparison($upload));

        if (
            $upload->isTraining() &&
            $validation->isStatus() &&
            ($this->trainingRecordService->lastTrainingDateUploadDateComparison($upload) || !$upload->isForcedDisplay())
        ) {
            $this->logger->info('ValidationService::updateValidationAndUploadStatus() - $upload->isTraining() && $validation->isStatus() && ($this->trainingRecordService->lastTrainingDateUploadDateComparison($upload) || !$upload->isForcedDisplay())');
            $this->trainingRecordService->updateTrainingRecord($upload);
            $this->logger->info('ValidationService::updateValidationAndUploadStatus() - Sending approval email to uploader');
            $this->mailerService->sendApprovalEmail($validation);
        } elseif ($validation->isStatus() === true) {
            $this->mailerService->sendApprovalEmail($validation);
        }
    }





    /**
     * Resets the approbation process for an upload.
     *
     * This method resets the validation status of an upload and handles the approbation instances
     * associated with it. It can reset all approbations or only those that were not approved,
     * depending on the modification type. It also handles sending notification emails and
     * updating training records if needed.
     *
     * @param Upload $upload The upload entity whose approbation process needs to be reset
     * @param Request $request The HTTP request containing modification details and comments
     * @param bool|null $globalModification Whether this is part of a global modification process.
     *                                     If true, sends a notification email to all approbators.
     *                                     Defaults to false.
     *
     * @return void This method doesn't return any value
     */
    public function resetApprobation(Upload $upload, Request $request, ?bool $globalModification = false)
    {
        $this->logger->info('ValidationService::resetApprobation() - uploadId: ' . $upload->getId());
        if ($upload->getValidation() == null) {
            return;
        }
        // Set the validated property of the Upload instance to null
        $upload->setValidated(null);
        // Get the ID of the validation instance
        $validation = $upload->getValidation();
        // Remove the Validation status from the database
        $validation->setStatus(null);

        // Store the comment in a variable
        $comment = $request->request->get('modificationComment');

        // If the user added a comment persist the comment
        $validation->setComment($comment);

        // Persist the Validation instance to the database
        $this->em->persist($validation);
        // Flush changes to the database
        $this->em->flush();

        // If the Validation instance has Approbation instances
        if ($validation->getApprobations() != null) {
            // Create an empty array to store Approbation instances
            $approbations = [];
            // Get the ID of the Validation instance
            $approbations = $validation->getApprobations();
            // $this->logger->info('Resetting approbations' . json_encode($approbations));
            // Loop through each Approbation instance
            foreach ($approbations as $approbation) {
                //If it's a major modification reset all approbations
                if ($request->request->get('modification-outlined') == 'heavy-modification') {
                    // Remove the Approbation instance from the database
                    $approbation->setApproval(null);
                    $approbation->setApprovedAt(null);
                    $approbation->setComment(null);
                    if (!$globalModification) {
                        $this->mailerService->sendDisapprovedModifiedEmail($approbation);
                    }
                }
                // If the Approbation is not approved or the Approval property is null
                elseif ($approbation->isApproval() === false) {
                    // Remove the Approbation instance from the database
                    $approbation->setApproval(null);
                    $approbation->setApprovedAt(null);
                    $approbation->setComment(null);
                    $this->mailerService->sendDisapprovedModifiedEmail($approbation);
                }

                // Persist the Approbation instance to the database
                $this->em->persist($approbation);
            }
        }

        if ($globalModification) {
            $this->mailerService->approbationEmail($validation);
        }

        // Flush changes to the database
        $this->em->flush();

        $this->logger->info('display-needed: ' . $request->request->get('display-needed') . ' training-needed: ' . $request->request->get('training-needed'));

        if ($request->request->get('display-needed') === 'true' && $request->request->get('training-needed') === 'true') {
            $this->trainingRecordService->updateTrainingRecord($upload);
        }
    }




    /**
     * Checks for pending validations and sends reminder emails to relevant users.
     *
     * This method runs on even-numbered days and checks for documents awaiting validation.
     * It sends reminder emails to validators with pending approvals, to uploaders whose
     * documents are waiting for validation or have been refused, and a general reminder
     * to all users about documents that have been waiting for validation for more than 14 days.
     * The method uses a tracking file to ensure emails are not sent multiple times on the same day.
     *
     * @param array $users An array of User entities to check for validator roles and send reminders to
     *
     * @return void This method doesn't return any value
     */
    public function remindCheck(array $users)
    {
        $today = new \DateTime();
        $fileName = 'email_sent.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;
        $uploadsWaitingValidationRaw = [];
        $uploaders = [];

        if (
            $this->params->get('kernel.environment') !== 'dev' &&
            ($today->format('d') % 2 == 0 &&
                (!file_exists($filePath) ||
                    strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false))
        ) {

            $nonValidatedValidations = $this->validationRepository->findNonValidatedValidations();

            $uploadsWaitingValidationRaw = $this->uploadsWaitingValidationRaw(
                nonValidatedValidations: $nonValidatedValidations,
                today: $today
            );

            $validators = $this->validatorsDelimitation(users: $users);

            foreach ($validators as $validator) {

                $approbationsNotAnswered = $this->approbationRepository->findBy([
                    'UserApprobator' => $validator,
                    'approval' => null
                ]);

                $approbationsRefused = $this->approbationRepository->findBy([
                    'UserApprobator' => $validator,
                    'approval' => false
                ]);

                $uploaders[] = $this->defineUploadersOfUploadsWaitingValidation(
                    approbationsNotAnswered: $approbationsNotAnswered,
                    validator: $validator,
                    today: $today,
                    filePath: $filePath
                );

                $uploaders[] = $this->defineUploadersOfRefusedUploads(
                    approbationsRefused: $approbationsRefused,
                    today: $today,
                );
            }

            foreach ($uploaders as $uploader) {
                $this->mailerService->sendReminderEmailToUploader($uploader);
            }

            if (!empty($uploadsWaitingValidationRaw)) {
                $this->mailerService->sendReminderEmailToAllUsers($uploadsWaitingValidationRaw);
            }
        }
    }


    /**
     * Filters validations to identify uploads waiting for validation for 14 days or more.
     *
     * This method examines a list of non-validated validations and identifies those
     * whose associated uploads have been waiting for validation for at least 14 days.
     * It compares the upload date with the current date to determine the waiting period.
     *
     * @param array $nonValidatedValidations An array of Validation entities that have not been validated
     * @param \DateTime $today The current date used for comparison with upload dates
     *
     * @return array An array of Upload entities that have been waiting for validation for 14 days or more
     */
    private function uploadsWaitingValidationRaw(
        array $nonValidatedValidations,
        \DateTime $today
    ): array {
        foreach ($nonValidatedValidations as $validation) {
            $uploadedAt = $validation->getUpload()->getUploadedAt();
            if (date_diff($today, $uploadedAt)->days >= 14) {
                $uploadsWaitingValidationRaw[] = $validation->getUpload();
            }
        }
        return $uploadsWaitingValidationRaw;
    }


    /**
     * Filters users to identify those with validator roles.
     *
     * This method examines a list of users and identifies those who have
     * validator roles (ROLE_LINE_ADMIN_VALIDATOR or ROLE_ADMIN_VALIDATOR).
     * It creates a filtered list containing only users with these specific roles.
     *
     * @param array $users An array of User entities to check for validator roles
     *
     * @return array An array of User entities that have validator roles
     */
    private function validatorsDelimitation(array $users): array
    {
        $rolesToCheck = ['ROLE_LINE_ADMIN_VALIDATOR', 'ROLE_ADMIN_VALIDATOR'];
        foreach ($users as $user) {
            if (array_intersect($rolesToCheck, $user->getRoles())) {
                $validators[] = $user;
            }
        }
        return $validators;
    }


    /**
     * Identifies uploads waiting for validation and sends reminder emails to validators.
     *
     * This method processes approbations that have not been answered yet, identifies uploads
     * that have been waiting for validation for at least one day, sends reminder emails to
     * the validator responsible for those approbations, and collects the uploaders of those
     * documents for further notification processing.
     *
     * @param Collection $approbationsNotAnswered Collection of approbations that have not been answered
     * @param User $validator The validator user who needs to be reminded
     * @param \DateTime $today The current date used for comparison with upload dates
     * @param string $filePath Path to the tracking file that records when emails were sent
     *
     * @return array An associative array of uploaders indexed by their IDs, who have uploads
     *               waiting for validation by the specified validator
     */
    private function defineUploadersOfUploadsWaitingValidation(
        Collection $approbationsNotAnswered,
        User $validator,
        \DateTime $today,
        string $filePath
    ): array {
        $return = false;
        $uploadsWaitingValidation = [];

        foreach ($approbationsNotAnswered as $approbationNotAnswered) {
            $upload = $approbationNotAnswered->getValidation()->getUpload();
            $uploadedAt = $upload->getUploadedAt();
            if (date_diff($today, $uploadedAt)->days >= 1) {
                $uploadsWaitingValidation[] = $upload;
            }
        }

        if (!empty($uploadsWaitingValidation)) {
            $return = $this->mailerService->sendReminderEmail(
                $validator,
                $uploadsWaitingValidation
            );

            foreach ($uploadsWaitingValidation as $upload) {
                $uploaderId = $upload->getUploader()->getId();
                $uploaders[$uploaderId] = $upload->getUploader();
            }
        }

        if ($return) {
            file_put_contents($filePath, $today->format('Y-m-d'));
        }

        return $uploaders;
    }



    /**
     * Identifies uploads that have been refused and collects their uploaders.
     *
     * This method processes approbations that have been explicitly refused, identifies uploads
     * that have been refused for at least one day, and collects the uploaders of those
     * documents for notification purposes.
     *
     * @param Collection $approbationsRefused Collection of approbations that have been refused
     * @param \DateTime $today The current date used for comparison with upload dates
     *
     * @return array An associative array of uploaders indexed by their IDs, who have uploads
     *               that have been refused
     */
    private function defineUploadersOfRefusedUploads(
        Collection $approbationsRefused,
        \DateTime $today
    ): array {
        $uploadsRefused = [];

        foreach ($approbationsRefused as $approbationRefused) {
            $upload = $approbationRefused->getValidation()->getUpload();
            $uploadedAt = $upload->getUploadedAt();
            if (date_diff($today, $uploadedAt)->days >= 1) {
                $uploadsRefused[] = $upload;
            }
        }

        if (!empty($uploadsRefused)) {
            foreach ($uploadsRefused as $upload) {
                $uploaderId = $upload->getUploader()->getId();
                $uploaders[$uploaderId] = $upload->getUploader();
            }
        }
        return $uploaders;
    }


    
    /**
     * Performs a monthly quality check and sends a quality resume email.
     *
     * This function runs on even-numbered days of the month. It checks if a monthly
     * quality resume has already been sent for the current month. If not, it sends
     * a quality resume email and records the date of sending to prevent duplicate emails.
     *
     * The function uses a text file to track when the last email was sent, comparing
     * the month in the file with the current month to determine if a new email should be sent.
     *
     * @return void This function doesn't return any value
     */
    public function qualityCheckUp()
    {
        $today = new \DateTime();
        $fileName = 'quality_email_sent.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;

        if (
            $today->format('d') % 2 == 0 &&
            (
                !file_exists($filePath) ||
                strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false
            )
        ) {

            $dateString = '';
            $fileMonth = null;

            if (file_exists($filePath)) {
                $dateString = trim(file_get_contents($filePath));
                $dateFromFile = \DateTime::createFromFormat('Y-m-d', $dateString);
                $fileMonth = $dateFromFile ? $dateFromFile->format('m') : null;
            }

            $todayMonth = $today->format('m');

            if ($fileMonth != $todayMonth) {
                $return = $this->mailerService->monthlyQualityResume();
                if ($return) {
                    file_put_contents($filePath, $today->format('Y-m-d'));
                }
            }
        }
    }





    /**
     * Checks if the number of selected validators meets the required minimum.
     *
     * This function counts the number of validators selected in the request by looking
     * for form fields containing 'validator_user' in their names that have non-empty values.
     * It then compares this count against the required minimum number of validators.
     *
     * @param Request $request The HTTP request containing the form data with validator selections
     * @param int $neededValidator The minimum number of validators required
     *
     * @return bool Returns true if the number of selected validators is equal to or greater
     *              than the required minimum, false otherwise
     */
    public function checkNumberOfValidator(Request $request, Int $neededValidator): bool
    {

        $selectedValidatorsCount = 0;
        $enoughValidator = false;

        foreach ($request->request->keys() as $key) {
            // If the key contains 'validator_user', add its value to the validator_user_values array
            if (
                strpos($key, 'validator_user') !== false &&
                $request->request->get($key) !== null &&
                !empty($request->request->get($key))
            ) {
                $selectedValidatorsCount++;
            }
        }
        if ($selectedValidatorsCount >= $neededValidator) {
            $enoughValidator = true;
        }
        return $enoughValidator;
    }
}
