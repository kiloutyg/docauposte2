<?php

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;

use App\Entity\Upload;
use App\Entity\User;
use App\Entity\OldUpload;

use App\Service\FolderCreationService;
use App\Service\ValidationService;
use App\Service\OldUploadService;

// This class is used to manage the uploads files and logic
class UploadService extends AbstractController
{
    protected $uploadRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $buttonRepository;
    protected $folderCreationService;
    protected $validationService;
    protected $oldUploadService;


    public function __construct(
        FolderCreationService $folderCreationService,
        ButtonRepository $buttonRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository,
        LoggerInterface $logger,
        ValidationService $validationService,
        OldUploadService $oldUploadService
    ) {
        $this->uploadRepository      = $uploadRepository;
        $this->manager               = $manager;
        $this->projectDir            = $params->get('kernel.project_dir');
        $this->logger                = $logger;
        $this->buttonRepository      = $buttonRepository;
        $this->folderCreationService = $folderCreationService;
        $this->validationService     = $validationService;
        $this->oldUploadService      = $oldUploadService;
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


    // This function is responsible for the logic of deleting the uploads files
    public function deleteFile(int $uploadId)
    {
        $upload     = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        if ($upload->getOldUpload() != null) {
            $oldUploadId = $upload->getOldUpload()->getId();
            $this->oldUploadService->deleteOldFile($oldUploadId);
        }
        $filename   = $upload->getFilename();
        $name       = $filename;
        $public_dir = $this->projectDir . '/public';
        $button     = $upload->getButton();

        // Dynamic folder and file deletion
        $buttonname = $button->getName();
        $parts      = explode('.', $buttonname);
        $parts      = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $path = $folderPath . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
        $this->manager->remove($upload);
        $this->manager->flush();
        return $name;
    }


    // This function is responsible for the logic of modifying the uploads files
    public function modifyFile(Upload $upload, User $user, Request $request, string $oldFileName)
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

        $comment = $request->request->get('modificationComment');

        $preExistingValidation = !empty($upload->getValidation());

        ////////////// Part mainly important for the introduction of the validation process in the production environment
        // Check if the file need to be validated or not, by checking if there is a validator_user string in the request
        if ($request->request->get('validatorRequired') == 'true') {
            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_user') !== false) {
                    $validated = null;
                }
            }
            // Retire the old file
            $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
            // Set the validated boolean property
            $upload->setValidated($validated);
            // Update the uploader in the upload object
            $upload->setUploader($user);
            // Set the revision 
            $upload->setRevision(1);
            if ($preExistingValidation) {
                if ($validated === null) {
                    $this->validationService->updateValidation($upload, $request);
                }
            } else {
                if ($validated === null) {
                    $this->validationService->createValidation($upload, $request);
                }
            }
        } else {
            $validated = true;
        };
        // If new file exists, process it and delete the old one
        if ($newFile) {

            try { // Retire the old file
                $this->oldUploadService->retireOldUpload($oldFilePath, $oldFileName);
            } catch (\exception $e) {
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
                throw $e;
            }
            // Update the file path in the upload object
            $upload->setPath($Path);
            // Update the uploader in the upload object
            $upload->setUploader($user);
            // Reset the validation and approbation property
            $request->request->set('modification-outlined', 'heavy-modification');
            // If the modification is heavy, increment the revision number
            $upload->setRevision($upload->getRevision() + 1);
            // If the modification is heavy, reset the approbation and set the $globalModification flag to true
            $globalModification = true;
            if ($preExistingValidation) {
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



    // This function is responsible for the logic of grouping the uploads files by parent entities
    public function groupUploads($uploads)
    {
        $groupedUploads = [];

        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {
            $button = $upload->getButton();
            $category = $button->getCategory();
            $productLine = $category->getProductLine();
            $zone = $productLine->getZone();

            $zoneName        = $zone->getName();
            $productLineName = $productLine->getName();
            $categoryName    = $category->getName();
            $buttonName      = $button->getname();

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
        }
        return $groupedUploads;
    }


    // This function is responsible for the logic of grouping the uploads files by parent entities
    public function groupValidatedUploads($uploads)
    {
        $groupedValidatedUploads = [];

        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {

            if ($upload->getValidation()) {

                $button = $upload->getButton();
                $category = $button->getCategory();
                $productLine = $category->getProductLine();
                $zone = $productLine->getZone();

                $zoneName        = $zone->getName();
                $productLineName = $productLine->getName();
                $categoryName    = $category->getName();
                $buttonName      = $button->getname();

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

        return $groupedValidatedUploads;
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
}
