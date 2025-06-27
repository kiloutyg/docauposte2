<?php

namespace App\Service\Upload;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Upload;
use App\Entity\User;

use App\Service\Validation\ValidationService;
use App\Service\Upload\OldUploadService;
use App\Service\FolderService;
use App\Service\Upload\FileTypeService;

use Psr\Log\LoggerInterface;


/**
 * UploadModificationService - Manages file uploads and modifications in the document management system
 *
 * This service handles all aspects of file modification including:
 * - Processing new file uploads and replacements
 * - Managing file validation workflows
 * - Handling file versioning and revision tracking
 * - Coordinating approval processes
 * - Maintaining file history through the OldUpload system
 *
 * The service integrates with other specialized services to handle specific aspects
 * of the document management process, such as validation, file storage, and type checking.
 */
class UploadModificationService extends AbstractController
{
    /**
     * @var EntityManagerInterface Doctrine entity manager for database operations
     */
    private $em;

    /**
     * @var LoggerInterface Logger for recording service operations and errors
     */
    private $logger;

    /**
     * @var ValidationService Service for handling document validation processes
     */
    private $validationService;

    /**
     * @var OldUploadService Service for managing document version history
     */
    private $oldUploadService;

    /**
     * @var FolderService Service for managing file system operations and paths
     */
    private $folderService;

    /**
     * @var FileTypeService Service for validating and checking file types
     */
    private $fileTypeService;


    /**
     * Constructor for the UploadModificationService class.
     *
     * Initializes the service with required dependencies for managing file uploads,
     * validation, and file operations.
     *
     * @param EntityManagerInterface $em                The Doctrine entity manager for database operations
     * @param LoggerInterface $logger                   Logger service for error and activity logging
     * @param ValidationService $validationService      Service for handling file validation processes
     * @param OldUploadService $oldUploadService        Service for managing retired or replaced files
     * @param FolderService $folderService              Service for file path and directory operations
     * @param FileTypeService $fileTypeService          Service for file type validation and checking
     */
    public function __construct(
        EntityManagerInterface  $em,
        LoggerInterface         $logger,


        ValidationService       $validationService,
        OldUploadService        $oldUploadService,
        FolderService           $folderService,
        FileTypeService         $fileTypeService,
    ) {
        $this->em                    = $em;
        $this->logger                = $logger;

        $this->validationService     = $validationService;
        $this->oldUploadService      = $oldUploadService;
        $this->folderService         = $folderService;
        $this->fileTypeService       = $fileTypeService;
    }






    // This function is responsible for the logic of modifying the uploads files
    /**
     * Handles the modification of an uploaded file, including file replacement, validation status updates,
     * and metadata changes.
     *
     * This function processes file modifications by:
     * - Checking and updating validation requirements
     * - Processing new file uploads (if provided)
     * - Managing file paths and storage
     * - Updating revision numbers and metadata
     * - Handling validation resets for significant changes
     *
     * @param Upload $upload The upload entity to be modified
     * @param Request $request The HTTP request containing modification parameters and file data
     * @throws \Exception If file processing fails
     * @return void
     */
    public function modifyFile(Upload $upload, Request $request)
    {
        $this->logger->info('UploadModificationService::modifyFile : upload: ', [$upload->getId()]);
        $user = $this->getUser();

        $oldFilePath = $upload->getPath();
        $oldFileName = $upload->getFilename();

        // New file path
        $path = $this->folderService->uploadPath($upload);

        $modificationOutlined = $request->request->get('modification-outlined');

        $preExistingValidation = !empty($upload->getValidation());

        $newFile = $upload->getFile();

        $this->logger->info('UploadModificationService::modifyFile - Before modifiedUploadValidationChecker ');
        $this->modifiedUploadValidationChecker($request, $upload, $user);
        $this->logger->info('UploadModificationService::modifyFile - After modifiedUploadValidationChecker ');

        $upload->setTraining(
            filter_var($request->request->get('training-needed'), FILTER_VALIDATE_BOOLEAN)
        );
        $upload->setForcedDisplay(
            filter_var($request->request->get('display-needed'), FILTER_VALIDATE_BOOLEAN)
        );

        // If new file exists, process it and delete the old one
        if ($newFile) {
            try {
                // Retire the old file
                $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);

                // Check file type before processing
                $this->fileTypeService->checkFileType($newFile);

                // Remove old file if it exists
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                // Move the new file to the directory
                $newFile->move(
                    $this->folderService->pathFindingDoc(
                        $upload->getButton()->getName()
                    ) . '/',
                    $upload->getFilename()
                );

                // Update the file path in the upload object
                $upload->setPath($path);
                // Update the uploader in the upload object
                $upload->setUploader($user);

                // If the modification is heavy, increment the revision number
                $upload->setRevision($upload->getRevision() + 1);
                if ($modificationOutlined == '') {
                    // If the modification is heavy, reset the approbation and set the $globalModification flag to true
                    $globalModification = true;
                    // Reset the validation and approbation property
                    $request->request->set('modification-outlined', 'heavy-modification');
                }

                if ($preExistingValidation && ($modificationOutlined == null ||
                    $modificationOutlined == '' ||
                    $modificationOutlined == 'heavy-modification')) {
                    $this->validationService->resetApprobation(
                        $upload,
                        $request,
                        $globalModification
                    );
                }
            } catch (\Exception $e) {
                $this->logger->error('Error processing file modification: ' . $e->getMessage());
                throw $e;
            }
        } else {
            // If no new file is uploaded, just rename the old one if necessary
            if ($oldFilePath != $path) {
                rename($oldFilePath, $path);
                $upload->setPath($path);
                // Update the uploader in the upload object
                $upload->setUploader($user);
            }
        }

        // Persist changes and flush to the database
        $upload->setUploadedAt(new \DateTime());
        $this->em->persist($upload);
        $this->em->flush();
        $this->logger->notice(
            'UploadModificationService::modifyFile: Fichier modifié avec succès. ',
            [
                "Uploader username" => $user->getUsername(),
                "Upload Name" => $upload->getFilename(),
                "Full request" => $request->request->all()
            ]
        );
    }






    /**
     * Checks and processes validation requirements for a modified upload
     *
     * This function determines whether validation is required for a modified upload
     * and routes the processing to the appropriate handler based on the validation
     * requirements. It checks if validation is explicitly requested in the form
     * submission and whether the upload already has existing validation records.
     *
     * @param Request $request The HTTP request containing validation parameters and form data
     * @param Upload $upload The upload entity being modified and checked for validation
     * @param User $user The user performing the modification
     * @return void
     */
    public function modifiedUploadValidationChecker(Request $request, Upload $upload, User $user): void
    {
        $this->logger->info(
            'UploadModificationService::modifiedUploadValidationChecker: request: ',
            [$request->request->all()]
        );

        $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);
        $this->logger->info(
            'UploadModificationService::modifiedUploadValidationChecker: newValidation: ',
            [$newValidation]
        );

        $preExistingValidation = !empty($upload->getValidation());
        $this->logger->info(
            'UploadModificationService::modifiedUploadValidationChecker: preExistingValidation: ',
            [$preExistingValidation]
        );

        if ($newValidation) {
            $this->handleValidationRequired(
                $request,
                $upload,
                $user,
                $preExistingValidation
            );
        } else {
            $this->handleNoNewValidationRequired(
                $request,
                $upload,
                $preExistingValidation
            );
        }
    }





    /**
     * Processes an upload that requires validation
     *
     * This function handles the validation workflow for uploads that require approval,
     * determining the validation status based on request parameters and existing validation
     * records. It updates the upload's validation status, uploader information, and revision
     * number, and triggers validation record creation or updates when necessary.
     *
     * @param Request $request The HTTP request containing validation parameters and form data
     * @param Upload $upload The upload entity requiring validation processing
     * @param User $user The user performing the upload or modification
     * @param bool $preExistingValidation Whether the upload already had validation records
     * @return void
     */
    private function handleValidationRequired(Request $request, Upload $upload, User $user, bool $preExistingValidation): void
    {
        $this->logger->info('UploadModificationService::handleValidationRequired');

        $validationNeededResponse = $this->determineIfValidationIsNeeded($request, $upload, $preExistingValidation);
        $this->logger->notice(
            'UploadModificationService::handleValidationRequired: validationNeededResponse: ',
            [$validationNeededResponse]
        );

        $validationNeededResponse ? $validationNeeded = null : $validationNeeded = true;
        $this->logger->debug(
            'UploadModificationService::handleValidationRequired: validationNeeded: ',
            [$validationNeeded]
        );

        $modificationOutlined = $request->request->get('modification-outlined');
        $this->logger->info(
            'UploadModificationService::handleValidationRequired: modificationOutlined: ',
            [$modificationOutlined]
        );

        // Set the validated boolean property
        $upload->setValidated($validationNeeded);

        // Update the uploader in the upload object
        $upload->setUploader($user);

        // Set the revision
        $upload->setRevision(1);

        if ($validationNeeded === null) {
            $this->updateOrCreateValidation(
                $upload,
                $request,
                $preExistingValidation,
                $modificationOutlined
            );
        }
    }



    /**
     * Handle the case when no validation is required for the upload
     *
     * Processes an upload that doesn't require validation by checking for modification comments
     * and updating existing validation records if applicable.
     *
     * @param Request $request The HTTP request containing modification comments
     * @param Upload $upload The upload entity being processed
     * @param bool $preExistingValidation Whether the upload already had validation records
     * @return void
     */
    private function handleNoNewValidationRequired(Request $request, Upload $upload, bool $preExistingValidation): void
    {
        $this->logger->info(
            'UploadModificationService::handleNoNewValidationRequired'
        );
        $comment = $request->request->get('modificationComment');
        $this->logger->info(
            'UploadModificationService::handleNoNewValidationRequired: comment: ',
            [$comment]
        );

        if ($preExistingValidation && $comment != null) {
            $this->updateExistingValidationComment($upload, $request, $comment);
        }
    }



    /**
     * Updates the comment on an existing validation record for an upload
     *
     * Updates the comment field of an existing validation record, appending
     * a note if the modification is classified as minor. The updated validation
     * is then persisted to the database.
     *
     * @param Upload $upload The upload entity whose validation comment needs updating
     * @param Request $request The HTTP request containing modification type information
     * @param string $comment The new comment text to be applied to the validation
     * @return void
     */
    private function updateExistingValidationComment(Upload $upload, Request $request, string $comment): void
    {
        $this->logger->info(
            'UploadModification::updateExistingValidationComment()'
        );
        $modificationOutlined = $request->request->get('modification-outlined');

        if ($modificationOutlined == 'minor-modification') {
            $comment = $comment . ' (modification mineure)';
        }

        $preExistingValidationEntity = $upload->getValidation();
        $preExistingValidationEntity->setComment($comment);
        $this->em->persist($preExistingValidationEntity);
        $this->em->flush();
    }





    /**
     * Determines if validation is needed for an upload based on modification type and validator changes
     *
     * This function analyzes the request and upload context to decide whether validation
     * is required. It checks for two main scenarios:
     * 1. For non-minor modifications, it looks for validator user assignments in the request
     * 2. For minor modifications with existing validation, it checks if there are new approvers
     *
     * @param Request $request The HTTP request containing modification parameters and validator assignments
     * @param Upload $upload The upload entity being evaluated for validation requirements
     * @param bool $preExistingValidation Whether the upload already had validation records
     * @return bool|null True if validation is needed, false if not, null in special cases
     */
    private function determineIfValidationIsNeeded(
        Request $request,
        Upload $upload,
        bool $preExistingValidation
    ): ?bool {
        $this->logger->info('UploadModification::determineIfValidationIsNeeded()');

        (bool)$minorModification = $request->request->get('modification-outlined') === 'minor-modification';

        $response = false;

        if (!$minorModification) {
            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_user') !== false) {
                    $response = true; // Needs validation
                }
            }
        } elseif ($preExistingValidation && $minorModification) {
            $diffInApprobators = $this->validationService->checkApprobatorChange($request, $upload);
            $this->logger->info(
                'UploadModification::determineIfValidationIsNeeded() - Difference in approbators',
                [
                    'diffInApprobators' => $diffInApprobators,
                    'newApprobators' => $diffInApprobators['newApprobators'],
                    'removedApprobators' => $diffInApprobators['removedApprobators']
                ]
            );
            $response = count($diffInApprobators['newApprobators']) > 0; // Needs validation
        }

        $this->logger->info(
            'UploadModification::determineIfValidationIsNeeded() - results',
            [
                'full request' => $request->request->all(),
                'Response' => $response
            ]
        );

        return $response;
    }






    /**
     * Updates or creates validation records for an upload based on modification type
     *
     * Determines whether to update existing validation records or create new ones
     * based on whether the upload already has validation records and the type of
     * modification being performed.
     *
     * @param Upload $upload The upload entity requiring validation
     * @param Request $request The HTTP request containing validation parameters
     * @param bool $preExistingValidation Whether the upload already had validation records
     * @param string|null $modificationOutlined The type of modification being performed
     * @return void
     */
    private function updateOrCreateValidation(
        Upload $upload,
        Request $request,
        bool $preExistingValidation,
        ?string $modificationOutlined
    ): void {

        $this->logger->info('UploadModification::updateOrCreateValidation()');

        $this->logger->info(
            'UploadModification::updateOrCreateValidation(): preExistingValidation: ',
            [$preExistingValidation]
        );

        $this->logger->info(
            'UploadModification::updateOrCreateValidation(): modificationOutlined: ',
            [$modificationOutlined]
        );

        if ($preExistingValidation) {
            $this->logger->info(
                'UploadModification::updateOrCreateValidation(): Updating existing validation and isHeavyModification'
            );
            $this->validationService->updateValidation($upload, $request);
        } else {
            $this->logger->info(
                'UploadModification::updateOrCreateValidation(): Creating new validation and not isHeavyModification'
            );
            $this->validationService->createValidation($upload, $request);
        }
    }






    /**
     * Processes a previously disapproved file for resubmission
     *
     * Handles the reprocessing of a disapproved upload by replacing the file (if provided),
     * updating file paths, managing training status, resetting validation status,
     * and triggering the validation reset process.
     *
     * @param Upload $upload The upload entity to be modified after disapproval
     * @param User $user The user performing the modification
     * @param Request $request The HTTP request containing modification parameters
     * @param string|null $newFilename Optional new filename for the upload (not currently used)
     * @throws \Exception If file processing or moving fails
     * @return void
     */
    public function modifyDisapprovedFile(Upload $upload, User $user, Request $request, ?string $newFilename = null)
    {
        $trainingNeeded = filter_var($request->request->get('training-needed'), FILTER_VALIDATE_BOOLEAN);

        // Get the new file directly from the Upload object
        $newFile = $upload->getFile();

        // Old file path
        $oldFilePath = $upload->getPath();

        $path = $this->folderService->uploadPath($upload);
        // If new file exists, process it and delete the old one
        if ($newFile) {

            $this->fileTypeService->checkFileType($newFile);

            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
            // Move the new file to the directory
            try {
                $newFile->move($this->folderService->pathFindingDoc($upload->getButton()->getName()) . '/', $upload->getFilename());
            } catch (\Exception $e) {
                $this->logger->error('Error while ;moving the newfile: ' . $e->getMessage());
            }
            // Update the file path in the upload object
            $upload->setPath($path);
            // Update the uploader in the upload object
            $upload->setUploader($user);
        } else {
            // If no new file is uploaded, just rename the old one if necessary
            if ($oldFilePath != $path) {
                rename($oldFilePath, $path);
                $upload->setPath($path);
                // Update the uploader in the upload object
                $upload->setUploader($user);
            }
        }

        if ($upload->isTraining() != $trainingNeeded) {
            $upload->setTraining($trainingNeeded);
        }
        $upload->setValidated(null);
        $upload->setUploadedAt(new \DateTime());


        // Persist changes and flush to the database
        $this->em->persist($upload);
        $this->em->flush();

        $this->validationService->resetApprobation($upload, $request);
    }






    /**
     * Transforms an array of Upload entities into a structured array for display or API response
     *
     * This function processes Upload entities into a standardized format containing essential
     * information about each upload, including metadata and validation status. It extracts
     * and formats uploader information, validation status, and approbation details.
     *
     * @param array $uploads An array of Upload entities to be processed
     * @return array A structured array containing formatted upload data with the following keys:
     *               - id: The upload's unique identifier
     *               - filename: The uppercase filename
     *               - revision: The revision number
     *               - uploader: Information about the user who uploaded the file
     *               - uploadedAt: The formatted upload date
     *               - validated: The validation status
     *               - validations: An array of validation details including approver information,
     *                 approval status, comments, and approval timestamps
     */
    public function prepareUploadData(array $uploads): array
    {
        $processedUploads = [];

        foreach ($uploads as $upload) {
            $processedUpload = [
                'id' => $upload->getId(),
                'filename' => strtoupper($upload->getFilename()),
                'revision' => $upload->getRevision(),
                'uploader' => $upload->getUploader() ? [
                    'firstName' => explode('.', $upload->getUploader()->getUsername()[0]),
                    'lastName' => explode('.', $upload->getUploader()->getUsername()[1]),
                ] : 'inconnu',
                'uploadedAt' => $upload->getUploadedAt()->format('d/m/Y'),
                'validated' => $upload->isValidated(),
                'validations' => [],
            ];

            // Process validations
            if ($upload->getValidation()) {
                foreach ($upload->getValidation()->getApprobations() as $approbation) {
                    $processedApprobation = [
                        'approver' => [
                            'firstName' => ucfirst(explode('.', $approbation->getUserapprobator()->getUsername())[0]),
                            'lastName' => strtoupper(explode('.', $approbation->getUserapprobator()->getUsername())[1]),
                        ],
                        'approval' => $approbation->isApproval(),
                        'comment' => $approbation->getComment(),
                        'approvedAt' => $approbation->getApprovedAt() ? $approbation->getApprovedAt()->format('d/m/Y H\hi') : null,
                    ];
                    $processedUpload['validations'][] = $processedApprobation;
                }
            }
            $processedUploads[] = $processedUpload;
        }
        return $processedUploads;
    }
}
