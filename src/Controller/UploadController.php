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


// This controlle is responsible for managing the logic of the upload interface
#[Route('/', name: 'app_')]
class UploadController extends FrontController
{
    // This function is responsible for rendering the upload interface
    #[Route('/upload', name: 'upload')]
    public function index(): Response
    {
        return $this->render('/services/uploads/upload.html.twig', []);
    }

    // This function is responsible for rendering the uploaded files interface
    #[Route('/uploaded', name: 'uploaded_files')]
    public function uploaded_files(): Response
    {

        return $this->render(
            'services/uploads/uploaded.html.twig'
        );
    }

    // Create a route to upload a file, and pass the request to the UploadsService to handle the file upload
    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(UploadsService $uploadsService, Request $request): Response
    {
        $this->uploadsService = $uploadsService;

        $originUrl = $request->headers->get('Referer');

        // Retrieve the User object
        $user = $this->getUser();

        // Retrieve the button and the newFileName from the request
        $button = $request->request->get('button');
        $newFileName = $request->request->get('newFileName');
        $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);

        // Check if the file already exists by comparing the filename and the button
        $conflictFile = '';
        $filename = '';
        $file = $request->files->get('file');
        if ($newFileName) {
            $filename   = $newFileName;
        } else {
            $filename   = $file->getClientOriginalName();
        }
        $conflictFile = $this->uploadRepository->findOneBy(['button' => $buttonEntity, 'filename' => $filename]);

        // if it exists, return an error message
        if ($conflictFile) {
            $this->addFlash('error', 'Le fichier ' . $filename . ' existe déjà.');
            return $this->redirect($originUrl);

            // if it does not exist pass the request to the service 
        } else if ($request->isMethod('POST')) {

            // Use the UploadsService to handle file uploads
            $name = $this->uploadsService->uploadFiles($request, $buttonEntity, $user, $newFileName);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirect($originUrl);
        }
    }


    // create a route to download a file in more simple terms to display the file
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

    public function delete_file(string $filename = null, int $button = null, UploadsService $uploadsService, Request $request): RedirectResponse
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);
        $originUrl = $request->headers->get('Referer');

        // Use the UploadsService to handle file deletion
        $name = $uploadsService->deleteFile($filename, $buttonEntity);
        $this->addFlash('success', 'Le fichier  ' . $name . ' a été supprimé.');

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

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        // Retrieve the User object
        $user = $this->getUser();

        // Check if there is a file to modify
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
                $uploadsService->modifyFile($upload, $user);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirect($originUrl);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirect($originUrl);
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