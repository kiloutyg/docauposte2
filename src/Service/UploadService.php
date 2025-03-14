<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UploadRepository;

use App\Entity\Upload;
use App\Entity\User;

use App\Service\ValidationService;
use App\Service\OldUploadService;
use App\Service\SettingsService;
use App\Service\FolderService;
use App\Service\FileTypeService;

// This class is used to manage the uploads files and logic
class UploadService extends AbstractController
{
    private $em;

    private $uploadRepository;

    private $validationService;
    private $oldUploadService;
    private $settingsService;
    private $folderService;
    private $fileTypeService;


    public function __construct(
        EntityManagerInterface  $em,

        UploadRepository        $uploadRepository,

        ValidationService       $validationService,
        OldUploadService        $oldUploadService,
        SettingsService         $settingsService,
        FolderService           $folderService,
        FileTypeService         $fileTypeService,
    ) {
        $this->em                    = $em;

        $this->uploadRepository      = $uploadRepository;

        $this->validationService     = $validationService;
        $this->oldUploadService      = $oldUploadService;
        $this->settingsService       = $settingsService;
        $this->folderService         = $folderService;
        $this->fileTypeService       = $fileTypeService;
    }

    // This function is responsible for the logic of uploading the uploads files
    public function uploadFiles(Request $request, $button, User $user, $filename)
    {
        // Get all the files from the request
        $files = $request->files->all();

        // Iterate over each file
        foreach ($files as $file) {

            $this->fileTypeService->checkFileType($file);

            // Construct the full path of the file
            $folderPath = $this->folderService->pathFindingDoc($button->getName());
            $path = $folderPath . '/' . $filename;
            // Move the file to the specified folder
            $file->move($folderPath . '/', $filename);

            // Create a new Upload object
            $upload = new Upload();

            // Set the file property using the path
            $upload->setFile(new File($path));
            // Set the filename property
            $upload->setFilename($filename);
            // Set the path property
            $upload->setPath($path);
            // Set the button property
            $upload->setButton($button);
            // Set the uploader property
            $upload->setUploader($user);
            // Set the uploadedAt property to the current date and time
            $upload->setUploadedAt(new \DateTime());

            // Set the revision property
            $upload->setRevision(1);

            // Persist the upload object
            $this->em->persist($upload);

            // Set training and validation related stuff
            $this->uploadTrainingValidationChecker($request, $upload);
        }

        // Save the changes to the database
        $this->em->flush();

        // Return the name of the last uploaded file
        return $filename;
    }



    public function uploadTrainingValidationChecker(Request $request, Upload $upload): void
    {
        // Check if the file need to be validated or not, by checking if there is a validator_department or a validator_user string in the request
        if ($request->request->get('validatorRequired') == 'true') {
            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_user') !== false) {
                    $validated = null;
                }
            }
        } else {
            $validated = true;
        };

        if ($request->request->get('training-needed') === 'true') {
            $trainingNeeded = true;
        } else {
            $trainingNeeded = false;
        }

        if ($request->request->get('display-needed') === 'true') {
            $displayNeeded = true;
        } else {
            $displayNeeded = false;
        }

        // Set the training property
        $upload->setTraining($trainingNeeded);
        // Set the display property
        $upload->setForcedDisplay($displayNeeded);
        // Set the validated boolean property
        $upload->setValidated($validated);

        if ($validated === null) {
            $this->validationService->createValidation($upload, $request);
        }
    }


    // This function is responsible for the logic of modifying the uploads files
    public function modifyFile(Upload $upload, Request $request)
    {

        // Get the new file directly from the Upload object
        $newFile = $upload->getFile();

        $user = $this->getUser();

        $oldFilePath = $upload->getPath();
        $oldFileName = $upload->getFilename();

        // New file path
        $path = $this->folderService->uploadPath($upload);

        $modificationOutlined = $request->request->get('modification-outlined');

        $preExistingValidation = !empty($upload->getValidation());

        // Retire the old file if a new one has been uploaded.
        if (!empty($newfile)) {
            try {
                $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        $this->modifyUploadTrainingValidationChecker($request, $upload, $user);

        $upload->setTraining(filter_var($request->request->get('training-needed'), FILTER_VALIDATE_BOOLEAN));

        $upload->setForcedDisplay(filter_var($request->request->get('display-needed'), FILTER_VALIDATE_BOOLEAN));


        // If new file exists, process it and delete the old one
        if ($newFile) {

            try { // Retire the old file
                $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
            } catch (\exception $e) {
                throw $e;
            }

            $this->fileTypeService->checkFileType($newFile);

            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
            // Move the new file to the directory
            try {
                $newFile->move($this->folderService->pathFindingDoc($upload->getButton()->getName()) . '/', $upload->getFilename());
            } catch (\Exception $e) {
                throw $e;
            }
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

            if ($preExistingValidation  && ($modificationOutlined == null || $modificationOutlined == '' || $modificationOutlined == 'heavy-modification')) {
                $this->validationService->resetApprobation($upload, $request, $globalModification);
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
    }



    // public function modifyUploadTrainingValidationChecker(Request $request, Upload $upload, User $user): void
    // {
    //     $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);
    //     $modificationOutlined = $request->request->get('modification-outlined');
    //     $preExistingValidation = !empty($upload->getValidation());
    //     if ($newValidation) {

    //         foreach ($request->request->keys() as $key) {
    //             if (strpos($key, 'validator_user') !== false) {
    //                 $validated = null;
    //             }
    //         }

    //         // Set the validated boolean property
    //         $upload->setValidated($validated);
    //         // Update the uploader in the upload object
    //         $upload->setUploader($user);
    //         // Set the revision 
    //         $upload->setRevision(1);

    //         if ($preExistingValidation && ($modificationOutlined == null || $modificationOutlined == '' || $modificationOutlined == 'heavy-modification')) {
    //             if ($validated === null) {
    //                 $this->validationService->updateValidation($upload, $request);
    //             }
    //         } else {
    //             if ($validated === null) {
    //                 $this->validationService->createValidation($upload, $request);
    //             }
    //         }
    //     } else {
    //         $validated = true;
    //         $comment = $request->request->get('modificationComment');
    //         if ($preExistingValidation && $comment != null) {
    //             if ($modificationOutlined == 'minor-modification') {
    //                 $comment = $comment . ' (modification mineure)';
    //             }
    //             $preExistingValidationEntity = $upload->getValidation();
    //             $preExistingValidationEntity->setComment($comment);
    //             $this->em->persist($preExistingValidationEntity);
    //             $this->em->flush();
    //         }
    //     }
    // }

    public function modifyUploadTrainingValidationChecker(Request $request, Upload $upload, User $user): void
    {
        $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);
        $preExistingValidation = !empty($upload->getValidation());

        if ($newValidation) {
            $this->handleRequiredValidation($request, $upload, $user, $preExistingValidation);
        } else {
            $this->handleNoValidationRequired($request, $upload, $preExistingValidation);
        }
    }

    /**
     * Handle the case when validation is required for the upload
     */
    private function handleRequiredValidation(Request $request, Upload $upload, User $user, bool $preExistingValidation): void
    {
        $validated = $this->determineValidationStatus($request);
        $modificationOutlined = $request->request->get('modification-outlined');

        // Set the validated boolean property
        $upload->setValidated($validated);

        // Update the uploader in the upload object
        $upload->setUploader($user);

        // Set the revision 
        $upload->setRevision(1);

        if ($validated === null) {
            $this->updateOrCreateValidation($upload, $request, $preExistingValidation, $modificationOutlined);
        }
    }

    /**
     * Handle the case when no validation is required for the upload
     */
    private function handleNoValidationRequired(Request $request, Upload $upload, bool $preExistingValidation): void
    {
        $upload->setValidated(true); // TODO : Check if this is an issue

        $comment = $request->request->get('modificationComment');
        if ($preExistingValidation && $comment != null) {
            $this->updateExistingValidationComment($upload, $request, $comment);
        }
    }

    /**
     * Determine if the upload needs validation based on request parameters
     */
    private function determineValidationStatus(Request $request): ?bool
    {
        foreach ($request->request->keys() as $key) {
            if (strpos($key, 'validator_user') !== false) {
                return null; // Needs validation
            }
        }
        return true; // No validation needed
    }

    /**
     * Update or create validation records based on existing validation and modification type
     */
    private function updateOrCreateValidation(Upload $upload, Request $request, bool $preExistingValidation, ?string $modificationOutlined): void
    {
        $isHeavyModification = $modificationOutlined == null ||
            $modificationOutlined == '' ||
            $modificationOutlined == 'heavy-modification';

        if ($preExistingValidation && $isHeavyModification) {
            $this->validationService->updateValidation($upload, $request);
        } else {
            $this->validationService->createValidation($upload, $request);
        }
    }

    /**
     * Update the comment on an existing validation
     */
    private function updateExistingValidationComment(Upload $upload, Request $request, string $comment): void
    {
        $modificationOutlined = $request->request->get('modification-outlined');

        if ($modificationOutlined == 'minor-modification') {
            $comment = $comment . ' (modification mineure)';
        }

        $preExistingValidationEntity = $upload->getValidation();
        $preExistingValidationEntity->setComment($comment);
        $this->em->persist($preExistingValidationEntity);
        $this->em->flush();
    }


    // This function is responsible for the logic of modifying the uploads files
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
                throw $e;
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





    // This function is responsible for the logic of grouping the uploads files by parent entities
    public function groupAllUploads($uploads)
    {
        $groupedUploads = [];

        $groupedValidatedUploads = [];
        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {


            $zoneName        = $upload->getButton()->getCategory()->getProductLine()->getZone()->getName();
            $productLineName = $upload->getButton()->getCategory()->getProductLine()->getName();
            $categoryName    = $upload->getButton()->getCategory()->getName();
            $buttonName      = $upload->getButton()->getname();


            if (!isset($groupedUploads[$zoneName])) {
                $groupedUploads[$zoneName] = [];
            }
            if (!isset($groupedUploads[$zoneName][$productLineName])) {
                $groupedUploads[$zoneName][$productLineName] = [];
            }
            if (!isset($groupedUploads[$zoneName][$productLineName][$categoryName])) {
                $groupedUploads[$zoneName][$productLineName][$categoryName] = [];
            }
            if (!isset($groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName])) {
                $groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName] = [];
            }
            $groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName][] = $upload;

            if ($upload->getValidation()) {
                if (!isset($groupedValidatedUploads[$zoneName])) {
                    $groupedValidatedUploads[$zoneName] = [];
                }
                if (!isset($groupedValidatedUploads[$zoneName][$productLineName])) {
                    $groupedValidatedUploads[$zoneName][$productLineName] = [];
                }
                if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName])) {
                    $groupedValidatedUploads[$zoneName][$productLineName][$categoryName] = [];
                }
                if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName])) {
                    $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName] = [];
                }
                $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName][] = $upload;
            }
        }
        return [$groupedUploads, $groupedValidatedUploads];
    }





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


    // create a route to redirect to the correct views of a file
    public function filterDownloadFile(int $uploadId, Request $request): Response
    {
        $upload = $this->uploadRepository->find($uploadId);
        $path = $upload->getPath();
        $settings = $this->settingsService->getSettings();
        if (!($settings->isUploadValidation() || $settings->IsTraining())) {
            return $this->downloadFileFromMethods($path);
        }

        $isValidated = $upload->isValidated();
        $isForcedDisplay = $upload->isForcedDisplay();
        $isTraining = $upload->isTraining();
        $hasOldUpload = $upload->getOldUpload() !== null;
        $originUrl = $request->headers->get('Referer');


        if ($isValidated === false && $isForcedDisplay === false) {
            if ($settings->IsTraining() && $isTraining) {
                return $this->redirectToRoute('app_training_front_by_validation', [
                    'validationId' => $upload->getValidation()->getId()
                ]);
            } elseif ($hasOldUpload) {
                return $this->downloadFileFromPath($uploadId);
            } else {
                $this->addFlash(
                    'Danger',
                    'Le fichier a été refusé par les validateurs et son affichage n\'est pas forcé. Contacter votre responsable pour plus d\'informations.'
                );
                return $this->redirect($originUrl, 307);
            }
        }

        if ($isValidated === null && $isForcedDisplay === false) {
            if ($hasOldUpload) {
                return $this->downloadFileFromPath($uploadId);
            } else {
                $this->addFlash(
                    'Danger',
                    'Le fichier est en cours de validation et son affichage n\'est pas forcé. Contacter votre responsable pour plus d\'informations.'
                );
                return $this->redirect($originUrl, 307);
            }
        }

        if ($isValidated === true) {
            if ($settings->IsTraining() && $isTraining) {
                return $this->redirectToRoute('app_training_front_by_upload', [
                    'uploadId' => $uploadId
                ]);
            } else {
                return $this->downloadFileFromMethods($path);
            }
        }

        if ($isValidated === null) {
            if ($settings->IsTraining() && $isTraining) {
                return $this->redirectToRoute('app_training_front_by_validation', [
                    'validationId' => $upload->getValidation()->getId()
                ]);
            } else {
                return $this->downloadFileFromPath($uploadId);
            }
        }
        return $this->downloadFileFromMethods($path);
    }






    public function downloadFileFromMethods(string $path): Response
    {
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    public function downloadFileFromPath(int $uploadId)
    {
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        $path = $this->determineFilePath($file);
        return $this->downloadFileFromMethods($path);
    }





    // Private method to determine the file path
    private function determineFilePath($file): string
    {
        if (!$file->isValidated()) {
            if ($file->isForcedDisplay() === true || $file->isForcedDisplay() === null) {
                return $file->getPath();
            } elseif ($file->getOldUpload() != null) {
                $oldUpload = $file->getOldUpload();
                return $oldUpload->getPath();
            }
        }
        return $file->getPath();
    }





    // create a route to download a file in more simple terms to display the file
    public function downloadInValidationFile(?int $uploadId = null)
    {
        // Retrieve the origin URL
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
