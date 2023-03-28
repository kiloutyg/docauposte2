<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;

use App\Entity\Upload;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;



#[Route('/', name: 'app_')]
class FrontController extends BaseController
{
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'zones'        => $this->zoneRepository->findAll(),
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
                'users'        => $this->userRepository->findAll(),
                'documents'    => $this->documentRepository->findAll(),
                'user'         => $this->getUser(),
            ]
        );
    }



    #[Route('/zone/{id}', name: 'zone')]
    public function zone(string $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $id]);

        return $this->render(
            'zone.html.twig',
            [

                'zone'         => $zone,
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
            ]
        );
    }



    #[Route('/zone/{name}/productline/{id}', name: 'productline')]
    public function productline(string $id = null): Response
    {

        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();
        return $this->render(
            'productline.html.twig',
            [
                'zone'        => $zone,

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }


    // Upload page for documents for a product line
    #[Route('/zone/{name}/productline/{id}/upload', name: 'productline_upload')]
    public function productlineUpload(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();
        return $this->render(
            'upload.html.twig',
            [
                'zone'        => $zone,

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }



    #[Route('/zone/{name}/productline/{id}/uploading', name: 'upload_files')]
    public function upload_files(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();

        foreach ($_FILES as $file) {
            // $productLineid = $productLine->getId();
            $public_dir = $this->getParameter('kernel.project_dir') . '/public';
            $filename   = $file['name'];
            $path       = $public_dir . '/doc/' . $filename;
            move_uploaded_file($file['tmp_name'], $path);

            $upload = new Upload();
            $upload->setFile(new File($path));
            $upload->setProductline($productLine);
            $upload->setFilename($file['name']);
            $upload->setPath($path);
            $upload->setUploadedAt(new \DateTime());
            $this->em->persist($upload);
        }
        $this->em->flush();

        return $this->redirectToRoute(
            'app_uploaded_files',
            [
                'id'   => $id,
                'name' => $zone->getName(),
            ]
        );
    }


    #[Route('/zone/{name}/productline/{id}/uploaded', name: 'uploaded_files')]
    public function uploaded_files(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();


        return $this->render(
            'uploaded.html.twig',
            [
                'zone'        => $zone,

                'productLine' => $productLine,

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


    // create a route to delete a file
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