namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use App\Repository\UploadRepository;

use App\Entity\Upload;

class UploadsService extends AbstractController
{

private $manager;
private $uploadRepository;

public function __construct(EntityManagerInterface $manager, UploadRepository $uploadRepository)
{
$this->manager = $manager;
$this->uploadRepository = $uploadRepository;
}

public function uploadFiles(Request $request, $button)
{
foreach ($_FILES as $file) {
$public_dir = $this->getParameter('kernel.project_dir') . '/public';
$filename = $file['name'];
$path = $public_dir . '/doc/' . $filename;
move_uploaded_file($file['tmp_name'], $path);

$files = $request->files->get('files');
$upload = new Upload();
$upload->setFile(new File($path));
$upload->setPath($path);
$upload->setButton($button);
$upload->setFile($files);
$upload->setUploadedAt(new \DateTime());
$this->manager->persist($upload);
}
$this->manager->flush();
}
}

<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use App\Repository\UploadRepository;
use App\Entity\Upload;

class UploadsService
{
    private $manager;
    private $uploadRepository;
    private $projectDir;

    public function __construct(EntityManagerInterface $manager, UploadRepository $uploadRepository, string $projectDir)
    {
        $this->manager = $manager;
        $this->uploadRepository = $uploadRepository;
        $this->projectDir = $projectDir;
    }

    public function uploadFiles(Request $request, $button)
    {
        $files = $request->files->all();

        foreach ($files as $file) {
            $public_dir = $this->projectDir . '/public';
            $filename   = $file->getClientOriginalName();
            $path       = $public_dir . '/doc/' . $filename;
            $file->move($public_dir . '/doc/', $filename);

            $upload = new Upload();
            $upload->setFile(new File($path));
            $upload->setPath($path);
            $upload->setButton($button);
            $upload->setUploadedAt(new \DateTime());
            $this->manager->persist($upload);
        }
        $this->manager->flush();
    }
}