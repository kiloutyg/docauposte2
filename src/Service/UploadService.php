<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UploadRepository;

use App\Entity\Upload;
use App\Entity\User;

use App\Service\ValidationService;
use App\Service\OldUploadService;
use App\Service\SettingsService;

// This class is used to manage the uploads files and logic
class UploadService extends AbstractController
{
    private $manager;
    private $projectDir;
    private $logger;

    private $uploadRepository;

    private $validationService;
    private $oldUploadService;
    private $settingsService;



    public function __construct(
        EntityManagerInterface  $manager,
        ParameterBagInterface   $params,
        LoggerInterface         $logger,

        UploadRepository        $uploadRepository,

        ValidationService       $validationService,
        OldUploadService        $oldUploadService,
        SettingsService         $settingsService
    ) {

        $this->manager               = $manager;
        $this->projectDir            = $params->get(name: 'kernel.project_dir');
        $this->logger                = $logger;

        $this->uploadRepository      = $uploadRepository;

        $this->validationService     = $validationService;
        $this->oldUploadService      = $oldUploadService;
        $this->settingsService       = $settingsService;
    }

    // This function is responsible for the logic of uploading the uploads files
    public function uploadFiles(Request $request, $button, User $user, $newFileName = null)
    {
        // Define the allowed file extensions
        $allowedExtensions = ['pdf'];
        // Get all the files from the request
        $files = $request->files->all();
        // Set the path to the 'public' directory
        $public_dir = $this->projectDir . '/public';
        // Iterate over each file
        foreach ($files as $file) {
            // Check if the file need to be validated or not, by checking if there is a validator_department or a validator_user string in the request
            if ($request->request->get('validatorRequired') == 'true') {
                foreach ($request->request->keys() as $key) {
                    // if (strpos($key, 'validator_department') !== false) {
                    //     $validated = null;
                    // } else
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

            // Dynamic folder creation and file upload
            // Get the name of the button
            $buttonname = $button->getName();
            // Split the button name into parts using '.'
            $parts = explode('.', $buttonname);
            // Reverse the order of the parts
            $parts = array_reverse($parts);
            // Create the base folder path
            $folderPath = $public_dir . '/doc';
            // Append the parts to the folder path
            foreach ($parts as $part) {
                $folderPath .= '/' . $part;
            }
            // Check if the file is of the right type
            // Get the file extension
            $extension = $file->guessExtension();
            // Check if the extension is in the list of allowed extensions
            if (!in_array($extension, $allowedExtensions)) {
                return $this->addFlash('error', 'Le fichier doit être un pdf');
            }
            // Check the MIME type of the file
            if ($file->getMimeType() != 'application/pdf') {
                return $this->addFlash('error', 'Le fichier doit être un pdf');
            }
            // Specify the revision number starting from 1
            $revision = 1;
            // Check if the user changed the file name or not and process it accordingly 
            // Initialize the filename variable
            $filename = '';
            // Check if a new filename is provided
            if ($newFileName) {
                $filename = $newFileName;
            } else {
                // Use the original filename of the file
                $filename = $file->getClientOriginalName();
            }
            // Construct the full path of the file
            $path = $folderPath . '/' . $filename;
            // Move the file to the specified folder
            $file->move($folderPath . '/', $filename);
            // Store the filename for return value
            $name = $filename;
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
            // Set the validated boolean property
            $upload->setValidated($validated);
            // Set the revision property
            $upload->setRevision($revision);
            // Set the training property
            $upload->setTraining($trainingNeeded);
            // Set the display property
            $upload->setForcedDisplay($displayNeeded);
            // Persist the upload object
            $this->manager->persist($upload);
        }

        // Save the changes to the database
        $this->manager->flush();

        $uploadEntity = $this->uploadRepository->findOneBy(['filename' => $filename, 'button' => $button]);
        if ($validated === null) {
            $this->validationService->createValidation($uploadEntity, $request);
        }
        // Return the name of the last uploaded file
        return $name;
    }





    // This function is responsible for the logic of modifying the uploads files
    public function modifyFile(Upload $upload, Request $request)
    {

        // $this->logger->info('fullrequest inside upload service', ['request' => $request->request->all()]);
        // Get the new file directly from the Upload object
        $newFile = $upload->getFile();
        // $this->logger->info('is newfile empty ' . empty($newfile));

        $user = $this->getUser();

        // Public directory
        $public_dir = $this->projectDir . '/public';

        // Old file path
        $oldFilePath = $upload->getPath();
        $oldFileName = $upload->getFilename();

        // New file path
        // Dynamic folder creation and file upload
        $buttonname = $upload->getButton()->getName();
        $parts      = explode('.', $buttonname);
        $parts      = array_reverse($parts);
        $folderPath = $public_dir . '/doc';
        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $Path = $folderPath . '/' . $upload->getFilename();

        $comment = $request->request->get('modificationComment');

        $modificationOutlined = $request->request->get('modification-outlined');
        // $this->logger->info('modification-outlined', ['modification-outlined' => $modificationOutlined]);

        $preExistingValidation = !empty($upload->getValidation());
        // $this->logger->info('preExistingValidation', ['preExistingValidation' => $preExistingValidation]);

        $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);

        if ($newValidation) {

            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_user') !== false) {
                    $validated = null;
                }
            }

            // Retire the old file if a new one has been uploaded.
            if (!empty($newfile)) {
                try {
                    // $this->logger->info('try to retire oldfile when validator is required');
                    $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
                } catch (\Exception $e) {
                    // $this->logger->info('Issues while retiring oldUpload' . $e);
                    throw $e;
                }
            }

            // Set the validated boolean property
            $upload->setValidated($validated);
            // Update the uploader in the upload object
            $upload->setUploader($user);
            // Set the revision 
            $upload->setRevision(1);

            if ($preExistingValidation && ($modificationOutlined == null || $modificationOutlined == '' || $modificationOutlined == 'heavy-modification')) {
                if ($validated === null) {
                    // $this->logger->info('Has an existing validation and is non minor modification');
                    $this->validationService->updateValidation($upload, $request);
                }
            } else {
                if ($validated === null) {
                    $this->validationService->createValidation($upload, $request);
                }
            }
        } else {
            $validated = true;
            if ($preExistingValidation && $comment != null) {
                if ($modificationOutlined == 'minor-modification') {
                    $comment = $comment . ' (modification mineure)';
                }
                $preExistingValidationEntity = $upload->getValidation();
                $preExistingValidationEntity->setComment($comment);
                $this->manager->persist($preExistingValidationEntity);
                $this->manager->flush();
            }
        };

        if ($request->request->get('training-needed') === 'true') {
            $upload->setTraining(true);
        } else {
            $upload->setTraining(false);
        }

        if ($request->request->get('display-needed') === 'true') {
            $upload->setForcedDisplay(true);
        } else {
            $upload->setForcedDisplay(false);
        }

        // If new file exists, process it and delete the old one
        if ($newFile) {

            try { // Retire the old file
                // $this->logger->info('try to retire oldfile when there is a newfile');

                $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
            } catch (\exception $e) {
                // $this->logger->info('Issues during retiring oldUpload' . $e);
                throw $e;
            }
            // Check if the file is of the right type
            if ($newFile->getMimeType() != 'application/pdf') {
                throw new \Exception('Le fichier doit être un pdf');
            }
            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
            // Move the new file to the directory
            try {
                $newFile->move($folderPath . '/', $upload->getFilename());
            } catch (\Exception $e) {
                // $this->logger->info('issues while moving the file to path' . $e);
                throw $e;
            }
            // Update the file path in the upload object
            $upload->setPath($Path);
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
            if ($oldFilePath != $Path) {
                rename($oldFilePath, $Path);
                $upload->setPath($Path);
                // Update the uploader in the upload object
                $upload->setUploader($user);
            }
        }
        // Persist changes and flush to the database
        $upload->setUploadedAt(new \DateTime());
        $this->manager->persist($upload);
        $this->manager->flush();
        return;
    }




    // This function is responsible for the logic of modifying the uploads files
    public function modifyDisapprovedFile(Upload $upload, User $user, Request $request)
    {

        // Get the new file directly from the Upload object
        $newFile = $upload->getFile();
        // Public directory
        $public_dir = $this->projectDir . '/public';
        // Old file path
        $oldFilePath = $upload->getPath();
        // New file path
        // Dynamic folder creation and file upload
        $buttonname = $upload->getButton()->getName();
        $parts      = explode('.', $buttonname);
        $parts      = array_reverse($parts);
        $folderPath = $public_dir . '/doc';
        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $Path = $folderPath . '/' . $upload->getFilename();

        // If new file exists, process it and delete the old one
        if ($newFile) {
            // Check if the file is of the right type
            if ($newFile->getMimeType() != 'application/pdf') {
                throw new \Exception('Le fichier doit être un pdf');
            }
            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
            // Move the new file to the directory
            try {
                $newFile->move($folderPath . '/', $upload->getFilename());
            } catch (\Exception $e) {
                throw $e;
            }
            // Update the file path in the upload object
            $upload->setPath($Path);
            // Update the uploader in the upload object
            $upload->setUploader($user);
        } else {
            // If no new file is uploaded, just rename the old one if necessary
            if ($oldFilePath != $Path) {
                rename($oldFilePath, $Path);
                $upload->setPath($Path);
                // Update the uploader in the upload object
                $upload->setUploader($user);
            }
        }
        $upload->setValidated(null);
        $upload->setUploadedAt(new \DateTime());

        // Persist changes and flush to the database

        $this->manager->persist($upload);
        $this->manager->flush();

        $this->validationService->resetApprobation($upload, $request);
    }


    // This function is responsible for the logic of grouping the uploads files by parent entities
    public function groupAllUploads($uploads)
    {
        $groupedUploads = [];

        $groupedValidatedUploads = [];
        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {

            // $this->logger->info('upload in groupAllUpload service', $upload);

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
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $path = $file->getPath();
        $settings = $this->settingsService->getSettings();
        if (!($settings->isUploadValidation() || $settings->IsTraining())) {
            return $this->downloadFileFromMethods($path);
        }

        $isValidated = $file->isValidated();
        $isForcedDisplay = $file->isForcedDisplay();
        $isTraining = $file->isTraining();
        $hasOldUpload = $file->getOldUpload() !== null;
        $originUrl = $request->headers->get('Referer');

        if ($isValidated === false && $isForcedDisplay === false) {
            if ($settings->IsTraining() && $isTraining) {
                return $this->redirectToRoute('app_training_front_by_validation', [
                    'validationId' => $file->getValidation()->getId()
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
                    'validationId' => $file->getValidation()->getId()
                ]);
            } else {
                return $this->downloadFileFromPath($uploadId);
            }
        }

        // Default action if none of the above conditions are met
        return $this->downloadFileFromMethods($path);
    }






    public function downloadFileFromMethods(string $path): Response
    {
        // $this->logger->info('path', ['path' => $path]);
        $file = new File($path);
        // $this->logger->info('file', ['file' => $file]);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }






    public function downloadFileFromPath(int $uploadId)
    {
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        $path = $this->determineFilePath($file);
        // $this->logger->info('path', ['path' => $path]);
        return $this->downloadFileFromMethods($path);
    }





    // Private method to determine the file path
    private function determineFilePath($file): string
    {

        if (!$file->isValidated()) {
            // $this->logger->info('is validated', ['validated' => $file->isValidated()]);
            if ($file->isForcedDisplay() === true || $file->isForcedDisplay() === null) {
                // $this->logger->info('is forced display on or null', ['forcedDisplay' => $file->isForcedDisplay()]);
                return $file->getPath();
            } elseif ($file->getOldUpload() != null) {
                // $this->logger->info('is old upload', ['oldUpload' => $file->getOldUpload()]);
                $oldUpload = $file->getOldUpload();
                return $oldUpload->getPath();
            }
        }
        return $file->getPath();
    }





    // create a route to download a file in more simple terms to display the file
    public function downloadInValidationFile(int $uploadId = null)
    {
        // Retrieve the origin URL
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
