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
use App\Service\UploadsService;

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



    #[Route('/zone/{name}/productline/{id}/category/{category}/button/{button}/uploaded', name: 'uploaded_files')]
    public function uploaded_files(string $button = null): Response
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['name' => $button]);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        return $this->render(
            'services/uploads/uploaded.html.twig',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'productLine' => $productLine,
                'id'          => $productLine->getName(),
                'category'    => $category->getName(),
                'categories'  => $this->categoryRepository->findAll(),
                'button'      => $buttonEntity,
                'uploads'     => $this->uploadRepository->findAll(),



            ]
        );
    }


    #[Route('/zone/{name}/productline/{id}/category/{category}/button/{button}/uploading', name: 'upload_files')]
    public function upload_files(UploadsService $uploadsService, Request $request, string $button = null): Response
    {
        $this->uploadsService = $uploadsService;

        $buttonEntity = $this->buttonRepository->findoneBy(['name' => $button]);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();


        // Use the UploadsService to handle file uploads
        $name = $this->uploadsService->uploadFiles($request, $buttonEntity);
        $this->addFlash('success', 'The file '  . $name .  'has been uploaded successfully!');

        return $this->redirectToRoute(
            'app_uploaded_files',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'productLine' => $productLine,
                'id'          => $productLine->getName(),
                'category'    => $category->getName(),
                'categories'  => $this->categoryRepository->findAll(),
                'button'      => $buttonEntity->getName(),
                'uploads'     => $this->uploadRepository->findAll(),
            ]
        );
    }




    // create a route to download a file
    #[Route('/zone/{name}/productline/{id}/category/{category}/button/{button}/download/{filename}', name: 'download_file')]
    public function download_file(string $filename = null): Response
    {
        $public_dir = $this->getParameter('kernel.project_dir') . '/public';
        $path       = $public_dir . '/doc/' . $filename;
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // create a route to delete a file. I'll need to refactor of move this function elsewhere

    #[Route('/zone/{name}/productline/{id}/category/{category}/button/{button}/delete/{filename}', name: 'delete_file')]
    public function delete_file(string $filename = null, string $button = null, UploadsService $uploadsService): Response
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['name' => $button]);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        $name = $uploadsService->deleteFile($filename, $buttonEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirectToRoute(
            'app_uploaded_files',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'productLine' => $productLine,
                'id'          => $productLine->getName(),
                'category'    => $category->getName(),
                'categories'  => $this->categoryRepository->findAll(),
                'button'      => $buttonEntity->getName(),
                'uploads'     => $this->uploadRepository->findAll(),
            ]
        );
    }