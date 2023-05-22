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

class UploadsService extends AbstractController
{

    protected $uploadRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $buttonRepository;

    public function __construct(ButtonRepository $buttonRepository, EntityManagerInterface $manager, ParameterBagInterface $params, UploadRepository $uploadRepository, LoggerInterface $logger)
    {
        $this->uploadRepository = $uploadRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;
        $this->buttonRepository = $buttonRepository;
    }

    public function uploadFiles(Request $request, $button, $newFileName = null)
    {
        $allowedExtensions = ['pdf'];
        $files = $request->files->all();

        foreach ($files as $file) {

            $extension = $file->guessExtension();

            if (!in_array($extension, $allowedExtensions)) {
                // throw new \Exception('Le fichier doit être au format PDF');
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }

            $public_dir = $this->projectDir . '/public';

            if ($newFileName) {
                $filename   = $newFileName;
            } else {
                $filename   = $file->getClientOriginalName();
            }

            // Add .pdf extension if it is missing
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'pdf') {
                $filename .= '.pdf';
            }

            $path       = $public_dir . '/doc/' . $filename;
            $file->move($public_dir . '/doc/', $filename);
            $name = $filename;

            $upload = new Upload();
            $upload->setFile(new File($path));
            $upload->setFilename($filename);
            $upload->setPath($path);
            $upload->setButton($button);
            $upload->setUploadedAt(new \DateTime());
            $this->manager->persist($upload);
        }
        $this->manager->flush();
        return $name;
    }

    public function deleteFile($filename, $button)
    {
        $name = $filename;
        $public_dir = $this->projectDir . '/public';
        $path       = $public_dir . '/doc/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }

        $upload = $this->uploadRepository->findOneBy(['filename' => $filename, 'button' => $button]);
        $this->manager->remove($upload);
        $this->manager->flush();
        return $name;
    }


    public function modifyFile(Upload $upload)
    {
        // Log the form data
        $this->logger->info('original upload state', ['upload' => $upload]);


        // Get the new file directly from the Upload object
        $newFile = $upload->getFile();

        // Continue as before...
        $public_dir = $this->projectDir . '/public';
        if (!file_exists($public_dir . '/doc/')) {
            mkdir($public_dir . '/doc/', 0777, true);
        }
        $oldFilePath = $upload->getPath();
        $Path = $public_dir . '/doc/' . $upload->getFilename();
        // ... and so on
        // Remove old file if it exists
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        // Move the new file to the directory
        try {
            if ($newFile) {
                $newFile->move($public_dir . '/doc/', $upload->getFilename());
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to move uploaded file: ' . $e->getMessage());
            throw $e;
        }

        $upload->setPath($Path);


        // Persist changes and flush to the database
        $upload->setUploadedAt(new \DateTime());
        $this->manager->persist($upload);
        $this->manager->flush();
    }
}