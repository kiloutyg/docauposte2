<?php

namespace App\Controller;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Form\UploadType;

use App\Service\UploadsService;

use App\Repository\ButtonRepository;


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
        $file = $this->uploadRepository->findOneBy(['filename' => $filename]);
        $path = $file->getPath();
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
    #[Route('/modifyfile/{uploadId}', name: 'modify_file_page')]

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



    // create a route to modify a file

    #[Route('/modify/{uploadId}', name: 'modify_file')]
    public function modify_file(Request $request, int $uploadId, UploadsService $uploadsService, ButtonRepository $buttonRepository, LoggerInterface $logger): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        if (!$upload) {
            $logger->error('Upload not found', ['uploadId' => $uploadId]);
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            // return $this->redirectToRoute('app_modify_file_page'); // or wherever you want to redirect
            return $this->redirectToRoute('app_base'); // or wherever you want to redirect

        }

        $logger->info('Retrieved Upload entity:', ['upload' => $upload]);

        // Get form data
        $formData = $request->request->all();
        $logger->info('Form data before any manipulation:', ['formData' => $formData]);



        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        $logger->info('Form data before manipulation:', ['formData' => $formData]);

        // Handle the form data on POST requests
        $logger->info('Form data before handleRequest:', ['formData' => $formData]);



        $form->handleRequest($request);





        $logger->info('Form data after handleRequest:', ['formData' => $form->getData()]);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                $uploadsService->modifyFile($upload);

                $this->addFlash('success', 'Le fichier a été modifié.');
                $logger->info('File modified successfully', ['upload' => $upload]);
                return $this->redirectToRoute('app_base');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification du fichier.');
                $logger->error('Failed to modify file', ['upload' => $upload, 'error' => $e->getMessage()]);

                $response = [
                    'status' => 'error',
                    'message' => 'Une erreur s\'est produite lors de la modification du fichier.',
                    'error' => $e->getMessage(),
                ];

                return new JsonResponse($response);
            }
        }


        // Convert the errors to an array
        $errorMessages = [];
        if ($form->isSubmitted() && !$form->isValid()) {
            // Get form errors
            $errors = $form->getErrors(true);

            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            // Print form errors
            $logger->error('Form validation errors:', ['errors' => $errorMessages]);

            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirectToRoute('app_base', ['uploadId' => $uploadId]);
        }

        // If it's a POST request but the form is not valid or not submitted
        if ($request->isMethod('POST')) {
            $this->addFlash('error', 'Invalid form. Errors: ' . implode(', ', $errorMessages));
            $logger->info('Submitted data:', $request->request->all());

            return $this->redirectToRoute('app_base', ['uploadId' => $uploadId]); // Return a 400 Bad Request response
        }

        // If it's a GET request, render the form
        return $this->render('services/uploads/uploads_modification.html.twig', [
            'form' => $form->createView(),

            'upload' => $upload
        ]);
    }
}