<?php

namespace App\Service\Upload;

use App\Entity\OldUpload;
use App\Entity\Upload;

use App\Repository\OldUploadRepository;
use App\Repository\UploadRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OldUploadService extends AbstractController
{
    protected $em;
    protected $oldUploadRepository;
    protected $uploadRepository;
    protected $projectDir;

    protected $logger;

    public function __construct(
        EntityManagerInterface $em,
        OldUploadRepository $oldUploadRepository,
        ParameterBagInterface $params,
        UploadRepository $uploadRepository,
        LoggerInterface $logger
    ) {
        $this->em                       = $em;
        $this->oldUploadRepository      = $oldUploadRepository;
        $this->uploadRepository         = $uploadRepository;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->logger                   = $logger;
    }


    /**
     * Retires an existing upload by creating an OldUpload entity.
     *
     * This function takes a file path and name, creates a copy of the file with an "Old_" prefix,
     * and associates it with the original upload as an OldUpload entity. If the upload already has
     * an associated old upload with identical content, no action is taken.
     *
     * @param string $oldFilePath The file path of the upload to be retired
     * @param string $oldFileName The file name of the upload to be retired
     *
     * @throws \Exception If there is an error copying the file
     *
     * @return void
     */
    public function retireOldUpload(string $oldFilePath, string $oldFileName)
    {
        $upload = $this->uploadRepository->findOneBy(['path' => $oldFilePath]);

        $this->logger->debug('OldUploadService: retireOldUpload: upload: ' . $upload->getId());
        $this->logger->debug('OldUploadService: retireOldUpload: upload to be retired name: ' . $upload->getFilename());

        $currentOldUpload = $upload->getOldUpload();
        if ($currentOldUpload !== null) {
            $this->logger->debug('OldUploadService: retireOldUpload: currentOldUpload: ' . $currentOldUpload->getId());
            $currendOldUploadEntity = $this->oldUploadRepository->find($currentOldUpload);
        }

        if ($currentOldUpload !== null && (file_get_contents($currendOldUploadEntity->getPath()) === file_get_contents($oldFilePath)) === true) {
            $this->logger->debug('OldUploadService: retireOldUpload: File exist and is the same as the current old file');
        } else {
            $this->logger->debug('OldUploadService: retireOldUpload: File is different from the current old file');

            $button             = $upload->getButton();
            $uploader           = $upload->getUploader();
            $filename           = $oldFileName;
            $oldFilename        = 'Old_' . $filename;

            $path = $oldFilePath;

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

            try {
                // Copy the file with the new name
                if (!copy($path, $oldPath)) {
                    $this->logger->error('Failed to copy file', [
                        'source' => $path,
                        'destination' => $oldPath
                    ]);
                }
                // The file has been successfully copied to $oldPath
            } catch (\Exception $e) {
                // This will catch any other unexpected exceptions that might occur
                $this->logger->error('Exception occurred while copying file', [
                    'source' => $path,
                    'destination' => $oldPath,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw the exception to be handled by the caller
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
            $this->em->persist($oldUpload);
            $this->em->flush();
        }
    }





    /**
     * Manages the display of an old upload.
     *
     * This function checks if the given upload has an associated old upload.
     * If the old upload is not found, it logs an error message, adds a flash message,
     * and redirects to the 'app_base' route.
     * If the old upload is found, it checks if it is validated.
     * If the old upload is validated, it redirects to the 'app_training_front_by_old_upload' route
     * with the old upload ID as a parameter.
     *
     * @param Upload $upload The upload to be managed.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     *     Returns a redirect response if the old upload is validated, or null if the old upload is not found.
     */
    public function manageOldUploadDisplay(Upload $upload)
    {
        $this->logger->debug(message: 'manageOldUploadDisplay');

        $oldUpload = $upload->getOldUpload();
        $this->logger->debug(message: 'manageOldUploadDisplay: oldUpload: ', context: [$oldUpload->getId()]);

        if ($oldUpload === null) {
            $this->logger->error(message: 'manageOldUploadDisplay: oldUpload is null');
            $this->addFlash(type: 'danger', message: 'Le fichier n\existe pas.');
            return $this->redirectToRoute(route: 'app_base');
        }
        return $this->redirectToRoute(
            route: 'app_training_front_by_old_upload',
            parameters: ['oldUploadId' => $oldUpload->getId()]
        );
    }
}
