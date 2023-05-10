<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Form\UploadType;

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



    #[Route('/uploaded', name: 'uploaded_files')]
    public function uploaded_files(): Response
    {

        return $this->render(
            'services/uploads/uploaded.html.twig'
        );
    }





    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(UploadsService $uploadsService, Request $request): Response
    {
        $this->uploadsService = $uploadsService;
        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Get the button and newFileName values from the submitted form data
            $button = $request->request->get('button');
            $newFileName = $request->request->get('newFileName');

            $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);

            // Use the UploadsService to handle file uploads
            $name = $this->uploadsService->uploadFiles($request, $buttonEntity, $newFileName);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');

            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'       => $this->zoneRepository->findAll(),
                    'productlines' => $this->productLineRepository->findAll(),
                    'categories'  => $this->categoryRepository->findAll(),
                    'buttons'     => $this->buttonRepository->findAll(),
                    'uploads'     => $this->uploadRepository->findAll(),
                ]
            );
        } else {
            // Show an error message if the form is not submitted

            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'       => $this->zoneRepository->findAll(),
                    'productlines' => $this->productLineRepository->findAll(),
                    'categories'  => $this->categoryRepository->findAll(),
                    'buttons'     => $this->buttonRepository->findAll(),
                    'uploads'     => $this->uploadRepository->findAll(),
                ]
            );
            // Redirect the user to an appropriate page or show an error message
        }
    }


    // create a route to download a file
    #[Route('/download/{filename}', name: 'download_file')]
    public function download_file(string $filename = null): Response
    {

        $public_dir = $this->getParameter('kernel.project_dir') . '/public';
        $path       = $public_dir . '/doc/' . $filename;
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // create a route to delete a file. I'll need to refactor of move this function elsewhere

    #[Route('/delete/{button}/{filename}', name: 'delete_file')]
    public function delete_file(string $filename = null, string $button = null, UploadsService $uploadsService): Response
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);


        $name = $uploadsService->deleteFile($filename, $buttonEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirectToRoute(
            'app_base',
            [
                'zones'       => $this->zoneRepository->findAll(),
                'productlines' => $this->productLineRepository->findAll(),
                'categories'  => $this->categoryRepository->findAll(),
                'buttons'     => $this->buttonRepository->findAll(),
                'uploads'     => $this->uploadRepository->findAll(),
            ]
        );
    }

    // create a route to display the modification page 
    #[Route('/modify/{uploadId}', name: 'modify_file_page')]

    public function modify_file_page(string $uploadId = null): Response
    {
        $upload = $this->uploadRepository->findoneBy(['id' => $uploadId]);
        $form = $this->createForm(UploadType::class, $upload);

        return $this->render(
            'services/uploads/uploads_modification.html.twig',
            [
                'upload' => $upload,

                'form' => $form->createView(),

            ]
        );
    }

    // create a route to modify an existing file

    #[Route('/modify/{uploadId}', name: 'modify_file')]
    public function modify_file(Request $request, int $uploadId, UploadsService $uploadsService): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        if (!$upload) {
            throw $this->createNotFoundException('No upload found for id ' . $uploadId);
        }

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            $uploadsService->modifyFile($upload, $form->getData());

            $this->addFlash('success', 'Le fichier a été modifié.');

            return $this->redirectToRoute('app_base');
        }

        return $this->render('services/uploads/uploads_modification.html.twig', [
            'form' => $form->createView(),
            'upload' => $upload

        ]);
    }
}