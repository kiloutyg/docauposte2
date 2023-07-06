<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        return $this->render('/services/uploads/upload.html.twig', []);
    }



    #[Route('/uploaded', name: 'uploaded_files')]
    public function uploaded_files(): Response
    {

        return $this->render(
            'services/uploads/uploaded.html.twig'
        );
    }

    // create a route to upload a file
    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(UploadsService $uploadsService, Request $request): Response
    {
        $this->uploadsService = $uploadsService;

        $originUrl = $request->headers->get('Referer');

        // Check if the form is submitted
        if ($request->isMethod('POST')) {

            $button = $request->request->get('button');
            $newFileName = $request->request->get('newFileName');

            $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);

            // Use the UploadsService to handle file uploads
            $name = $this->uploadsService->uploadFiles($request, $buttonEntity, $newFileName);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');

            return $this->redirect($originUrl);
        } else {
            // Show an error message if the form is not submitted

            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirect($originUrl);
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


    // create a route to delete a file
    #[Route('/delete/upload/{button}/{filename}', name: 'delete_file')]

    public function delete_file(string $filename = null, string $button = null, UploadsService $uploadsService, Request $request): RedirectResponse
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['name' => $button]);
        $originUrl = $request->headers->get('Referer');

        // Use the UploadsService to handle file deletion
        $name = $uploadsService->deleteFile($filename, $buttonEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirect($originUrl);
    }



    // create a route to modify a file and or display the modification page
    #[Route('/modify/{uploadId}', name: 'modify_file')]
    public function modify_file(Request $request, int $uploadId, UploadsService $uploadsService): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $button = $upload->getButton();
        $category = $button->getCategory();
        $productLine = $category->getProductLine();
        $zone = $productLine->getZone();
        $originUrl = $request->headers->get('Referer');

        if (!$upload) {
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirect($originUrl);
        }

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                $uploadsService->modifyFile($upload);

                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirect($originUrl);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification du fichier.');

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
            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirect($originUrl);
        }

        // If it's a POST request but the form is not valid or not submitted
        if ($request->isMethod('POST')) {
            $this->addFlash('error', 'Invalid form. Errors: ' . implode(', ', $errorMessages));
            return $this->redirect($originUrl); // Return a 400 Bad Request response
        }

        // If it's a GET request, render the form
        return $this->render('services/uploads/uploads_modification.html.twig', [
            'form' => $form->createView(),
            'zone'        => $zone,
            'productLine' => $productLine,
            'category'    => $category,
            'button'      => $button,
            'upload' => $upload
        ]);
    }
}