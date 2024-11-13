<?php

namespace App\Service;

use App\Entity\OldUpload;
use App\Entity\Upload;
use App\Entity\Button;

use App\Repository\OldUploadRepository;
use App\Repository\UploadRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



class OldUploadService extends AbstractController
{
    protected $manager;
    protected $oldUploadRepository;
    protected $uploadRepository;
    protected $projectDir;

    protected $logger;

    public function __construct(
        EntityManagerInterface $manager,
        OldUploadRepository $oldUploadRepository,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository,
        LoggerInterface $logger
    ) {
        $this->manager                  = $manager;
        $this->oldUploadRepository      = $oldUploadRepository;
        $this->uploadRepository         = $uploadRepository;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->logger                   = $logger;
    }


    // public function retireOldUpload(Upload $upload)
    public function retireOldUpload(string $OldFilePath, string $OldFileName)

    {
        $upload = $this->uploadRepository->findOneBy(['path' => $OldFilePath]);

        // $this->logger->info('OldUploadService: retireOldUpload: upload: ' . $upload->getId());
        // $this->logger->info('OldUploadService: retireOldUpload: upload to be retired name: ' . $upload->getFilename());

        $currentOldUpload = $upload->getOldUpload();
        if ($currentOldUpload !== null) {
            // $this->logger->info('OldUploadService: retireOldUpload: currentOldUpload: ' . $currentOldUpload->getId());
            $currendOldUploadEntity = $this->oldUploadRepository->find($currentOldUpload);
        }

        if ($currentOldUpload !== null && (file_get_contents($currendOldUploadEntity->getPath()) === file_get_contents($OldFilePath)) === true) {
            // $this->logger->info('OldUploadService: retireOldUpload: File exist and is the same as the current old file');
        } else {
            // $this->logger->info('OldUploadService: retireOldUpload: File is different from the current old file');

            $button             = $upload->getButton();
            $uploader           = $upload->getUploader();
            $filename           = $OldFileName;
            $oldFilename        = 'Old_' . $filename;

            $path = $OldFilePath;

            // New file path
            $buttonname = $button->getName();
            $parts      = explode('.', $buttonname);
            $parts      = array_reverse($parts);
            $public_dir = $this->projectDir . '/public';
            $folderPath = $public_dir . '/doc';
            foreach ($parts as $part) {
                $folderPath .= '/' . $part;
            }
            $oldPath = $folderPath . '/' . $oldFilename;

            // Copy the file with the new name
            if (copy($path, $oldPath)) {
                // The file has been copied to $oldPath
            } else {
                // The file could not be copied
                throw new \Exception("File could not be copied.");
            }

            $uploadedAt         = $upload->getUploadedAt();
            $validated          = $upload->isValidated();
            $revision           = $upload->getRevision();

            $oldUpload = new OldUpload();
            $oldUpload->setFile(new File($oldPath));
            $oldUpload->setButton($button);
            $oldUpload->setOldUploader($uploader);
            $oldUpload->setFilename($oldFilename);
            $oldUpload->setPath($oldPath);
            $oldUpload->setValidated($validated);
            $oldUpload->setOldUploadedAt($uploadedAt);
            $oldUpload->setRevision($revision);
            $upload->setOldUpload($oldUpload);
            $this->manager->persist($oldUpload);
            $this->manager->flush();
        }
    }
}
