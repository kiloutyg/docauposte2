<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Upload;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Service\AccountService;

#[Route('/', name: 'app_')]

class UploadController extends FrontController
{
    #[Route('/upload', name: 'upload')]
    public function index(): Response
    {
        return $this->render('upload/index.html.twig', [
            'controller_name' => 'UploadController',
        ]);
    }

    // Upload page for documents for a product line
    #[Route('/zone/{name}/productline/{id}/upload', name: 'productline_upload')]
    public function productlineUpload(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();
        return $this->render(
            'uploads/upload.html.twig',
            [
                'zone'        => $zone,

                'name' => $zone->getName(),

                'productLine' => $productLine,

                'id' => $productLine->getName(),

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }



    #[Route('/uploading', name: 'upload_files')]
    // public function upload_files(string $id = null): Response
    // {
    //     $button = $this->buttonRepository->findoneBy(['name' => $id]);

    //     foreach ($_FILES as $file) {
    //         $public_dir = $this->getParameter('kernel.project_dir') . '/public';
    //         $filename   = $file['name'];
    //         $path       = $public_dir . '/doc/' . $filename;
    //         move_uploaded_file($file['tmp_name'], $path);

    //         $upload = new Upload();
    //         $upload->setFile(new File($path));
    //         $upload->setFilename($file['name']);
    //         $upload->setButton($button);
    //         $upload->setPath($path);
    //         $upload->setUploadedAt(new \DateTime());
    //         $this->em->persist($upload);
    //     }
    //     $this->em->flush();

    //     return $this->redirectToRoute(
    //         'app_uploaded_files',
    //         [
    //             'id'   => $id,
    //         ]
    //     );
    // }
    public function upload_files(string $id = null): Response
    {
        $button = $this->buttonRepository->findoneBy(['name' => $id]);

        foreach ($_FILES as $file) {
            $public_dir = $this->getParameter('kernel.project_dir') . '/public';
            $filename   = $file['name'];
            $path       = $public_dir . '/doc/' . $filename;
            move_uploaded_file($file['tmp_name'], $path);

            $upload = new Upload();
            $upload->setFile(new File($path));
            $upload->setFilename($file['name']);
            $upload->setButton($button);
            $upload->setPath($path);
            $upload->setUploadedAt(new \DateTime());
            $this->em->persist($upload);
        }
        $this->em->flush();

        return $this->redirectToRoute(
            'app_uploaded_files',
            [
                'id'   => $id,
            ]
        );
    }

    #[Route('/zone/{name}/productline/{id}/uploaded', name: 'uploaded_files')]
    public function uploaded_files(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();


        return $this->render(
            'uploads/uploaded.html.twig',
            [
                'zone'        => $zone,
                'name' => $zone->getName(),

                'productLine' => $productLine,
                'id' => $productLine->getName(),

                'uploads'     => $this->uploadRepository->findAll(),



            ]
        );
    }


    // create a route to download a file
    #[Route('/zone/{name}/productline/{id}/download/{filename}', name: 'download_file')]
    public function download_file(string $filename = null): Response
    {
        $public_dir = $this->getParameter('kernel.project_dir') . '/public';
        $path       = $public_dir . '/doc/' . $filename;
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // create a route to delete a file. I'll need to refactor of move this function elsewhere
    #[Route('/zone/{name}/productline/{id}/delete/{filename}', name: 'delete_file')]
    public function delete_file(string $filename = null, string $id = null): Response
    {
        $upload     = $this->uploadRepository->findOneBy(['filename' => $filename]);
        $public_dir = $this->getParameter('kernel.project_dir') . '/public';
        $path       = $public_dir . '/doc/' . $filename;

        $name = $filename;
        unlink($path);

        $this->em->remove($upload);
        $this->em->flush();
        $this->addFlash('success', 'File ' . $name . ' deleted');

        $productLine = $this->productLineRepository->findoneBy(['id' => $id]);
        $zone        = $productLine->getZone();

        return $this->redirectToRoute(
            'app_uploaded_files',

            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'id'          => $id,
                'productLine' => $productLine,
                'uploads'     => $this->uploadRepository->findAll(),

            ]
        );
    }
}