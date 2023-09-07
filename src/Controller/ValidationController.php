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
            $this->logger->info('form' . json_encode($form->getData()));
            $this->logger->info('form' . json_encode($form->getErrors()));
            $this->logger->info('request' . json_encode($request->request->all()));
            $this->logger->info('result' . json_encode($form->isSubmitted()));

            if ($form->isSubmitted() && $form->isValid()) {
                $this->logger->info('result' . json_encode($form->isValid()));

                $this->uploadService->modifyDisapprovedFile($upload, $user, $request);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirectToRoute('app_base');
            }
        }

        return $this->render('services/validation/disapprovedModification.html.twig', [
            'approbation'  => $approbation,
            'upload'       => $upload,
            'user'         => $this->getUser(),
            'form'         => $form->createView(),
            'approbations' => $approbations,

        ]);
    }
}