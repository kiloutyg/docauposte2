<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Repository\UploadRepository;

use App\Entity\Upload;

class UploadsService
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
        $files = $request->files->all();

        foreach ($files as $file) {
            $public_dir = $this->projectDir . '/public';
            if ($newFileName) {
                $filename   = $newFileName;
            } else {
                $filename   = $file->getClientOriginalName();
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
        unlink($path);

        $upload = $this->uploadRepository->findOneBy(['filename' => $filename, 'button' => $button]);
        $this->manager->remove($upload);
        $this->manager->flush();
        return $name;
    }
}