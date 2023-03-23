<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;


use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;



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
            ]
        );
    }

    #[Route('/zone/{id}', name: 'zone')]

    public function zone(int $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['id' => $id]);

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

        $productLine = $this->productLineRepository->findoneBy(['id' => $id]);
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

                // 'zones'       => $this->zoneRepository->findAll(),

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }

    #[Route('/zone/{name}/productline/{id}/upload', name: 'upload_files')]
    public function upload_files(string $id = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['id' => $id]);
        $zone        = $productLine->getZone();

        foreach ($_FILES as $file) {

            $productLineid = $productLine->getId();
            $public_dir    = $this->getParameter('kernel.project_dir') . '/public';
            $filename      = $file['name'];
            $path          = $public_dir . '/doc/' . $filename;
            move_uploaded_file($file['tmp_name'], $path);

            $document = new Document();
            $document->setFile(new File($path));
            $document->setProductline($productLineid);
            $document->setName($filename);
            $document->setPath($path);
            $document->setUploadedAt(new \DateTime());
            $this->em->persist($document);
        }
        $this->em->flush();

        return $this->redirectToRoute(
            'app_productline_upload',
            [
                'zone'        => $zone,

                // 'zones'       => $this->zoneRepository->findAll(),

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }


}