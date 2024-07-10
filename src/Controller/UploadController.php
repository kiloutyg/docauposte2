<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Form\UploadType;

use App\Entity\Upload;



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
    // 
    // 
    // 
    //     
    // 
    // Create a route to upload a file, and pass the request to the UploadService to handle the file upload
    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(Request $request): Response
    {
        $this->logger->info('fullrequest', ['request' => $request]);

        // Get the URL of the page from which the request originated
        $originUrl = $request->headers->get('Referer');
        // Retrieve the User object
        $user = $this->getUser();
        // Retrieve the button and the newFileName from the request
        $button      = $request->request->get('button');
        $newFileName = $request->request->get('newFileName');
        // Find the Button entity in the repository based on its ID
        $buttonEntity = $this->buttonRepository->findoneBy(['id' => $button]);
        // Check if the file already exists by comparing the filename and the button
        $conflictFile = '';
        $filename     = '';
        $file         = $request->files->get('file');
        if ($newFileName) {
            $filename = $newFileName;
        } else {
            $filename = $file->getClientOriginalName();
        }
        $conflictFile = $this->uploadRepository->findOneBy(['button' => $buttonEntity, 'filename' => $filename]);
        // If the file already exists, return an error message
        if ($conflictFile) {
            $this->addFlash('error', 'Le fichier ' . $filename . ' existe déjà.');
            return $this->redirect($originUrl);
        } else {
            // Use the UploadService to handle file uploads
            $name = $this->uploadService->uploadFiles($request, $buttonEntity, $user, $newFileName);
            $this->addFlash('success', 'Le document ' . $name . ' a été correctement chargé');
            return $this->redirect($originUrl);
        }
    }
    // 
    // 
    // 
    // 
    // create a route to download a file in more simple terms to display the file
    #[Route('/download/{uploadId}', name: 'download_file')]
    public function downloadFile(int $uploadId, Request $request): Response
    {
        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $file = $upload;
        if (!$file->isValidated()) {
            if ($file->isForcedDisplay() == true) {
                $path = $file->getPath();
                $file = new File($path);
                $this->addFlash('error', 'Le fichier est en cours de validation.');
                return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
            } else
            if ($file->getOldUpload() != null) {
                $oldUploadId = $file->getOldUpload()->getId();
                $oldUpload = $this->oldUploadRepository->findOneBy(['id' => $oldUploadId]);
                $path = $oldUpload->getPath();
                $file = new File($path);
                return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
            } else {
                $path = $file->getPath();
                $file = new File($path);
                $this->addFlash('error', 'Le fichier est en cours de validation.');
                return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
            }
            $this->addFlash('error', 'Le nouveau fichier est en cours de validation.');
            return $this->redirect($originUrl);
        }
        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
    // 
    // 
    // 
    // 
    // create a route to download a file in more simple terms to display the file
    #[Route('/download/invalidation/{uploadId}', name: 'download_invalidation_file')]
    public function downloadInvalidationFile(int $uploadId = null, Request $request): Response
    {
        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
    // 
    // 
    // 
    // 
    // create a route to delete a file
    #[Route('/delete/upload/{uploadId}', name: 'delete_file')]

    public function deleteFile(int $uploadId = null, Request $request): RedirectResponse
    {
        $originUrl = $request->headers->get('Referer');
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        // Check if the user is the creator of the upload or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN") || $this->getUser() === $upload->getUploader() || $upload->getUploader() === null) {
            // Use the UploadService to handle file deletion
            $name = $this->uploadService->deleteFile($uploadId);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce document.');
            return $this->redirect($originUrl);
        }

        $this->addFlash('success', 'Le fichier  ' . $name . ' a été supprimé.');
        return $this->redirect($originUrl);
    }
    // 
    // 
    // 
    // 
    // create a route to modify a file and or display the modification page
    #[Route('/modification/view/{uploadId}', name: 'modify_file')]
    public function fileModificationView(Request $request, int $uploadId): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload      = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $button      = $upload->getButton();
        $category    = $button->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        $currentUser = $this->security->getUser();
        $uploader = $upload->getUploader();
        // If it's a GET request, render the form
        if ($request->isMethod('GET') && ($currentUser === $uploader || $uploader === null || $this->authChecker->isGranted("ROLE_ADMIN"))) {
            return $this->render('services/uploads/uploads_modification.html.twig', [
                'form'        => $form->createView(),
                'zone'        => $zone,
                'productLine' => $productLine,
                'category'    => $category,
                'button'      => $button,
                'upload'      => $upload
            ]);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour modifier ce fichier. Contacter un administrateur ou le service informatique.');
            return $this->redirectToRoute('app_base');
        }
    }
    // 
    // 
    // 
    // 
    // Testing separting the post and get in two different method to see if the issue of not reloading the pages and persisting the comments can be resolved through
    // the reorganization of the code
    #[Route('/modification/modifying/{uploadId}', name: 'modifying_file')]
    public function modifyingFile(Request $request, int $uploadId): Response
    {
        // Log the request
        $this->logger->info('fullrequest', ['request' => $request->request->all()]);

        // Retrieve the current upload entity based on the uploadId
        $upload      = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $oldFileName = $upload->getFilename();

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        // Retrieve the User object
        $user = $this->getUser();

        // Check if there is a file to modify
        if (!$upload) {
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirectToRoute('app_modify_file', [
                'uploadId' => $uploadId
            ]);
        }

        $trainingNeeded =  $request->request->get('training-needed');
        $forcedDisplay = $request->request->get('forced-display');

        $this->logger->info('training needed', ['training' => $trainingNeeded]);
        $this->logger->info('forced display', ['forced-display' => $forcedDisplay]);

        $comment = $request->request->get('modificationComment');
        $newValidation = $request->request->get('validatorRequired');
        $enoughValidator = false;

        $enoughValidator = $request->request->has('validator_user4');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                if ($trainingNeeded == null || $forcedDisplay == null) {
                    if ($upload->getFile() && $upload->getValidation() != null && $comment == null && $comment == "") {
                        $this->addFlash('error', 'Le commentaire est vide. Commenter votre modification est obligatoire.');
                        return $this->redirectToRoute('app_modify_file', [
                            'uploadId' => $uploadId
                        ]);
                    } elseif ($newValidation == "true" && $enoughValidator == false) {
                        $this->addFlash('error', 'Selectionner au moins 4 validateurs pour valider le fichier.');
                        return $this->redirectToRoute('app_modify_file', [
                            'uploadId' => $uploadId
                        ]);
                    }
                }
                $this->uploadService->modifyFile($upload, $user, $request, $oldFileName);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirectToRoute('app_modify_file', [
                    'uploadId' => $uploadId
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('app_modify_file', [
                    'uploadId' => $uploadId
                ]);
            }
        }
        // Convert the errors to an array
        if ($form->isSubmitted() && !$form->isValid()) {
            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirectToRoute('app_modify_file', [
                'uploadId' => $uploadId
            ]);
        }
    }
}
