<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\All;



class ValidationController extends FrontController
{
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

            'upload'                => $upload,
            'user'                  => $this->getUser(),
        ]);
    }


    #[Route('/validation/approbation/{approbationId}', name: 'app_validation_approbation')]
    public function validationApprobationPage(
        int $approbationId = null
    ): Response {

        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);

        return $this->render('services/validation/approbation.html.twig', [

            'approbation'           => $approbation,
            'user'                  => $this->getUser(),
        ]);
    }


    #[Route('/validation/approbation/download/{approbationId}', name: 'app_validation_approbation_file')]
    public function validationDownloadFile(int $approbationId = null): Response
    {

        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        $validation = $approbation->getValidation();
        $file = $validation->getUpload();

        $path = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/validation/download/{uploadId}', name: 'app_validation_view_file')]
    public function validationFileView(int $UploadId = null, Request $request): Response
    {

        $file = $this->uploadRepository->findOneBy(['id' => $UploadId]);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        if (($file->isValidated()) === true) {
            $this->addFlash('error', 'Le fichier n\'a pas été validé.');
            return $this->redirect($originUrl);
        }
        $path = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/validation/approval/{approbationId}', name: 'app_validation_approval')]

    public function validationApproval(Request $request, int $approbationId = null): Response
    {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);

        $this->logger->info('approvalRequest' . json_encode($request->request->all()));

        $this->validationService->validationApproval($approbation, $request);

        $this->addFlash('success', 'Le fichier a été validé.');
        return $this->redirectToRoute('app_base');
    }

    #[Route('/validation/disapproved/modify/{approbationId}', name: 'app_validation_disapproved_modify')]

    public function disapprovedValidationModification(int $approbationId = null): Response
    {
        $validation = $this->validationRepository->findOneBy(['id' => $approbationId]);
        $upload = $validation->getUpload();

        return $this->render('services/validation/disapprovedModification.html.twig', [

            'upload'                => $upload,
            'user'                  => $this->getUser(),
        ]);
    }
}