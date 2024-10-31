<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Form\UploadType;

class ValidationController extends FrontController
{


    // Is not currently in use, but might get useful for the operator side validation. 
    #[Route('/validation/{uploadId}', name: 'app_validation')]
    public function validationViewBasePage(
        int $uploadId = null,
        Request $request
    ): Response {
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');
        if (($upload->isValidated()) === true) {
            $this->addFlash('error', 'Le fichier n\'a pas été validé.');
            return $this->redirect($originUrl);
        }

        return $this->render('services/validation/validation.html.twig', [

            'upload' => $upload,
            'user'   => $this->getUser(),
        ]);
    }


    #[Route('/validation/approbation/{approbationId}', name: 'app_validation_approbation')]
    public function validationApprobationPage(
        int $approbationId = null
    ): Response {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        if ($approbation->isApproval() !== null) {
            $this->addFlash('error', 'Une réponse à cette demande de validation a déja été fourni.');
            return $this->redirectToRoute('app_base');
        }
        return $this->render('services/validation/approbation.html.twig', [

            'approbation' => $approbation,
            'user'        => $this->getUser(),
        ]);
    }


    #[Route('/validation/approbation/download/{approbationId}', name: 'app_validation_approbation_file')]
    public function validationDownloadFile(int $approbationId = null): Response
    {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        $validation  = $approbation->getValidation();
        $file        = $validation->getUpload();

        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/validation/download/{uploadId}', name: 'app_validation_view_file')]
    public function validationFileView(int $uploadId = null, Request $request): Response
    {

        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        if (($file->isValidated()) === true) {
            $this->addFlash('error', 'Le fichier a été validé.');
            return $this->redirect($originUrl);
        } else {
            $path = $file->getPath();
            $file = new File($path);
            return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
        }
    }

    #[Route('/validation/approval/{approbationId}', name: 'app_validation_approval')]

    public function validationApproval(Request $request, int $approbationId = null): Response
    {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);

        $this->validationService->validationApproval($approbation, $request);

        $this->addFlash('success', 'Le fichier a été validé.');
        return $this->redirectToRoute('app_base');
    }



    #[Route('/validation/disapproved/modify/{approbationId}', name: 'app_validation_disapproved_modify')]
    public function disapprovedValidationModification(int $approbationId = null, Request $request): Response
    {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        $validation  = $approbation->getValidation();
        $upload      = $validation->getUpload();

        $approbations = [];
        $approbations = $validation->getApprobations(['Approval' => false]);

        $currentUser = $this->getUser();
        $user        = $this->userRepository->find($currentUser);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        $form = $this->createForm(UploadType::class, $upload, [
            'current_user_id'        => $user->getId(),
            'current_upload_id'      => $upload->getId(),
            'current_approbation_id' => $approbationId,
        ]);

        $form->remove('approbator');
        $form->remove('modificationType');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $this->uploadService->modifyDisapprovedFile($upload, $user, $request);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirectToRoute('app_base');
            }
        }
        if ($validation->isStatus() === false) {
            return $this->render('services/validation/disapprovedModification.html.twig', [
                'approbation'  => $approbation,
                'upload'       => $upload,
                'user'         => $this->getUser(),
                'form'         => $form->createView(),
                'approbations' => $approbations,

            ]);
        } else {
            $this->addFlash('error', 'Le fichier a bien été modifié.');
            return $this->redirect($originUrl);
        }
    }
    // 
    // 
    // 
    // 
    #[Route('/validation/disapproved/modifyByUpload/{uploadId}', name: 'app_validation_disapproved_modify_by_upload')]
    public function disapprovedValidationModificationByUpload(int $uploadId = null, Request $request): Response
    {
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $validation = $upload->getValidation();


        $approbations = [];
        $approbations = $validation->getApprobations(['Approval' => false]);

        $currentUser = $this->getUser();
        $user        = $this->userRepository->find($currentUser);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        $form = $this->createForm(UploadType::class, $upload, [
            'current_user_id'        => $user->getId(),
            'current_upload_id'      => $upload->getId(),
        ]);

        $form->remove('approbator');
        $form->remove('modificationType');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);


            if ($form->isSubmitted() && $form->isValid()) {
                $this->uploadService->modifyDisapprovedFile($upload, $user, $request);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirectToRoute('app_base');
            }
        }
        if ($validation->isStatus() === false) {
            return $this->render('services/validation/disapprovedModificationByUpload.html.twig', [
                'upload'       => $upload,
                'user'         => $this->getUser(),
                'form'         => $form->createView(),
                'approbations' => $approbations,

            ]);
        } else {
            $this->addFlash('error', 'Le fichier a bien été modifié.');
            return $this->redirect($originUrl);
        }
    }

    // 
    // 
    // 
    // 

    // public function validatedListUploadsRender(array $uploads = null): Response
    // {
    //     $this->logger->info('Uploads: ', [$uploads]);
    //     if (count($uploads) === 1) {

    //         $upload = $this->cacheService->getEntityById('upload', $uploads[0]->getId());
    //         $this->logger->info('Upload: ', [$upload]);
    //         $validation = $this->cacheService->getEntitiesByParentId('validation', $uploads[0]->getId());
    //         $this->logger->info('Validation: ', [$validation]);
    //         // $this->logger->info('Validation[0: ', [$validation[0]]);
    //         // $this->logger->info('Validationtoarray: ', [$validation->toArray()]);

    //         $approbations = $this->cacheService->getEntitiesByParentId('approbation', $validation->getId());
    //         // $approbations = $validation[0]->getApprobations();
    //     } else {
    //         $uploads = [];
    //         $approbations = [];
    //     }

    //     return $this->render('services/validation/validation_list_components/validation_list_button.html.twig', [
    //         'uploads'      => $uploads,
    //         'localApprobations' => $approbations,
    //     ]);
    // }
}