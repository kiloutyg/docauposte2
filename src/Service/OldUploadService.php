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

    public function __construct(
        EntityManagerInterface $manager,
        OldUploadRepository $oldUploadRepository,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository
    ) {
        $this->manager                  = $manager;
        $this->oldUploadRepository      = $oldUploadRepository;
        $this->uploadRepository         = $uploadRepository;
        $this->projectDir               = $params->get('kernel.project_dir');
    }


    public function retireOldUpload(Upload $upload)
    {

        if ($upload->getOldUpload() !== null) {
            return;
        } else {

            $button             = $upload->getButton();
            $displayOption      = $upload->getDisplayOption();
            $uploader           = $upload->getUploader();
            $filename           = $upload->getFilename();
            $oldFilename        = $filename . '_Old.pdf';

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
            $path = $folderPath . '/' . $filename;


            // $path               = $upload->getPath();
            // $oldPath            = $path . '_Old';

            // Copy the file with the new name
            if (copy($path, $oldPath)) {
                // The file has been copied to $oldPath
            } else {
                // The file could not be copied
                throw new \Exception("File could not be copied.");
            }


            $expiry_date        = $upload->getExpiryDate();
            $uploadedAt         = $upload->getUploadedAt();
            $validated          = $upload->isValidated();
            $revision           = $upload->getRevision();


            $oldUpload = new OldUpload();
            $oldUpload->setFile(new File($oldPath));
            $oldUpload->setButton($button);
            $oldUpload->setDisplayOption($displayOption);
            $oldUpload->setOldUploader($uploader);
            $oldUpload->setFilename($oldFilename);
            $oldUpload->setPath($oldPath);
            $oldUpload->setExpiryDate($expiry_date);
            $oldUpload->setValidated($validated);
            $oldUpload->setOldUploadedAt($uploadedAt);
            $oldUpload->setRevision($revision);
            $upload->setOldUpload($oldUpload);
            $this->manager->persist($oldUpload);
            $this->manager->flush();
        }
    }



    // This function is responsible for the logic of deleting the OldUploads files
    public function deleteOldFile(int $oldUploadId)
    {
        $oldUpload      = $this->oldUploadRepository->findOneBy(['id' => $oldUploadId]);
        $filename       = $oldUpload->getFilename();
        $name           = $filename;
        $public_dir     = $this->projectDir . '/public';
        $button         = $oldUpload->getButton();

        // Dynamic folder and file deletion
        $buttonname     = $button->getName();
        $parts          = explode('.', $buttonname);
        $parts          = array_reverse($parts);
        $folderPath     = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $path = $folderPath . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $this->manager->remove($oldUpload);
        $this->manager->flush();
        return $name;
    }
}