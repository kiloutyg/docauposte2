<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;

use App\Entity\Upload;
use App\Entity\Button;
use App\Entity\User;

use App\Service\FolderCreationService;


// This class is used to manage the uploads files and logic
class UploadsService extends AbstractController
{
    protected $uploadRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $buttonRepository;
    protected $folderCreationService;


    public function __construct(
        FolderCreationService $folderCreationService,
        ButtonRepository $buttonRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository,
        LoggerInterface $logger
    ) {
        $this->uploadRepository = $uploadRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;
        $this->buttonRepository = $buttonRepository;
        $this->folderCreationService = $folderCreationService;
    }

    // This function is responsible for the logic of uploading the uploads files
    public function uploadFiles(Request $request, $button, User $user, $newFileName = null)
    {
        $allowedExtensions = ['pdf'];
        $files = $request->files->all();
        $public_dir = $this->projectDir . '/public';

        foreach ($files as $file) {

            // Dynamic folder creation and file upload
            $buttonname = $button->getName();
            $parts = explode('.', $buttonname);
            $parts = array_reverse($parts);
            $folderPath = $public_dir . '/doc';

            foreach ($parts as $part) {
                $folderPath .= '/' . $part;
            }

            // Check if the file is of the right type
            $extension = $file->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }
            if ($file->getMimeType() != 'application/pdf') {
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }

            // Check if the user changed the file name or not and process it accordingly 
            $filename = '';
            if ($newFileName) {
                $filename   = $newFileName;
            } else {
                $filename   = $file->getClientOriginalName();
            }

            $path       = $folderPath . '/' . $filename;
            $file->move($folderPath . '/', $filename);

            $name = $filename;

            $upload = new Upload();
            $upload->setFile(new File($path));
            $upload->setFilename($filename);
            $upload->setPath($path);
            $upload->setButton($button);
            $upload->setUploader($user);
            $upload->setUploadedAt(new \DateTime());
            $this->manager->persist($upload);
        }
        $this->manager->flush();
        return $name;
    }


    // This function is responsible for the logic of deleting the uploads files
    public function deleteFile($filename, $buttonEntity)
    {
        $name = $filename;
        $public_dir = $this->projectDir . '/public';
        $button = $this->buttonRepository->findoneBy(['id' => $buttonEntity]);

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

        $upload = $this->uploadRepository->findOneBy(['filename' => $filename, 'button' => $button]);
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
}