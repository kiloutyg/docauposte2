<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ValidationController extends FrontController
{
    #[Route('/validation/{uploadId}', name: 'app_validation')]
    public function validationBasePage(int $uploadId = null, Request $request): Response
    {
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');
        if (($upload->isValidated()) === true) {
            $this->addFlash('error', 'Le fichier n\'a pas été validé.');
            return $this->redirect($originUrl);
        }

        return $this->render('services/validation/validation.html.twig', [
            'zones'                 => $this->zones,
            'productLines'          => $this->productLines,
            'categories'            => $this->categories,
            'buttons'               => $this->buttons,
            'uploads'               => $this->uploads,
            'upload'                => $upload,
            'users'                 => $this->users,
            'incidents'             => $this->incidents,
            'incidentCategories'    => $this->incidentCategories,
            'departments'           => $this->departments,
            'user'                  => $this->getUser(),
        ]);
    }



    #[Route('/validation/download/{uploadId}', name: 'app_validation_download_file')]
    public function download_file(int $uploadId = null, Request $request): Response
    {
        // Retrieve the origin URL
        $originUrl = $request->headers->get('Referer');

        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        if (($file->isValidated()) === true) {
            $this->addFlash('error', 'Le fichier n\'a pas été validé.');
            return $this->redirect($originUrl);
        }
        $path = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}