<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



use App\Repository\UploadRepository;

use App\Entity\Upload;

class UploadsService extends AbstractController
{

    protected $uploadRepository;
    protected $manager;
    protected $projectDir;

    public function __construct(EntityManagerInterface $manager, ParameterBagInterface $params, UploadRepository $uploadRepository)
    {
        $this->uploadRepository = $uploadRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
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

    public function modifyFile(Request $request, $button, $newFileName = null)
    {
        $allowedExtensions = ['pdf'];
        $files = $request->files->all();
        $name = null;  // Initialize $name to null

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

            // Find existing upload entity
            $pastupload = $this->uploadRepository->findOneBy(['filename' => $filename]);
            $pastbutton = $this->uploadRepository->findOneBy(['button' => $button]);

            // If no existing upload entity, create a new one
            if (!$pastupload and !$pastbutton) {
                $upload = new Upload();
            } else {
                // get the existing upload to update it
                $upload = $pastupload;
            }

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
}