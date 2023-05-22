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


    public function modifyFile(Upload $upload, array $formData)
    {
        // Log the form data
        $this->logger->info('original upload state', ['upload' => $upload]);

        $this->logger->info('Form data4: ', ['formData' => $formData]);


        // Check if a new file was uploaded
        if (isset($formData['upload']['file']) && $formData['upload']['file']) {
            $newFile = $formData['upload']['file'];
            $public_dir = $this->projectDir . '/public';

            $oldFilePath = $upload->getPath();
            $newFilePath = $public_dir . '/doc/' . $upload->getFilename();

            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Move the new file to the directory
            try {
                $newFile->move($public_dir . '/doc/', $upload->getFilename());
            } catch (\Exception $e) {
                $this->logger->error('Failed to move uploaded file: ' . $e->getMessage());
                throw $e;
            }

            $upload->setPath($newFilePath);
        } else {
            $this->logger->info('No file was uploaded.');
        }

        // // Check if filename is provided
        // if (isset($formData['upload']['filename']) && !empty($formData['upload']['filename'])) {
        //     // Only update the filename if it wasn't already updated by the form
        //     if ($upload->getFilename() !== $formData['upload']['filename']) {
        //         // Update the filename
        //         $upload->setFilename($formData['upload']['filename']);
        //     }
        // } else {
        //     // $this->logger->info('No filename was provided.');
        //     $this->logger->info('No filename was provided. Keeping the original filename.');
        //     $formData['upload']['filename'] = $upload->getFilename();
        // }

        // if (
        //     isset($formData['upload']['button']) && !empty($formData['upload']['button'])
        // ) {
        //     // instanceof Button
        //     $newButton = $this->buttonRepository->findOneBy(['id' => $formData['upload']['button']]);

        //     if ($upload->getButton() !== $newButton) {
        //         $upload->setButton($newButton);
        //     } else {
        //         $this->logger->info('No button was provided or it\'s not a Button entity.');
        //     }
        // }


        // Persist changes and flush to the database
        $upload->setUploadedAt(new \DateTime());
        $this->manager->persist($upload);
        $this->manager->flush();
    }
}