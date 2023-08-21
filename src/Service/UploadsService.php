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
use App\Entity\Button;
use App\Entity\User;

use App\Service\FolderCreationService;
use App\Service\ValidationService;


// This class is used to manage the uploads files and logic
class UploadsService extends AbstractController
{
    protected $uploadRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $buttonRepository;
    protected $folderCreationService;
    protected $validationService;


    public function __construct(
        FolderCreationService $folderCreationService,
        ButtonRepository $buttonRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository,
        LoggerInterface $logger,
        validationService $validationService
    ) {
        $this->uploadRepository = $uploadRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;
        $this->buttonRepository = $buttonRepository;
        $this->folderCreationService = $folderCreationService;
        $this->validationService = $validationService;
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
            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_department') !== false) {
                    $validated = null;
                } elseif (strpos($key, 'validator_user') !== false) {
                    $validated = null;
                } else {
                    $validated = true;
                }
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

            // Check if the user changed the file name or not and process it accordingly 

            // Initialize the filename variable
            $filename = '';

            // Check if a new filename is provided
            if ($newFileName) {
                $filename   = $newFileName;
            } else {
                // Use the original filename of the file
                $filename   = $file->getClientOriginalName();
            }

            // Construct the full path of the file
            $path       = $folderPath . '/' . $filename;

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
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $filename = $upload->getFilename();
        $name = $filename;
        $public_dir = $this->projectDir . '/public';
        $button = $upload->getButton();

        // Dynamic folder and file deletion
        $buttonname = $button->getName();
        $parts = explode('.', $buttonname);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $path       = $folderPath . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $this->manager->remove($upload);
        $this->manager->flush();
        return $name;
    }


    // This function is responsible for the logic of modifying the uploads files
    public function modifyFile(Upload $upload, User $user)
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
        $parts = explode('.', $buttonname);
        $parts = array_reverse($parts);
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
        // Persist changes and flush to the database
        $upload->setUploadedAt(new \DateTime());
        $this->manager->persist($upload);
        $this->manager->flush();
    }


    // This function is responsible for the logic of grouping the uploads files by parent entities
    public function groupUploads($uploads)
    {

        $groupedUploads = [];

        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {
            $zoneName = $upload->getButton()->getCategory()->getProductLine()->getZone()->getName();
            $productLineName = $upload->getButton()->getCategory()->getProductLine()->getName();
            $categoryName = $upload->getButton()->getCategory()->getName();
            $buttonName = $upload->getButton()->getName();

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
        $parts = explode('.', $buttonname);
        $parts = array_reverse($parts);
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

        $this->validationService->resetApprobation($upload);
    }
}