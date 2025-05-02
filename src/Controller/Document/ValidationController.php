<?php

namespace App\Controller\Document;

use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpFoundation\File\File;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\ApprobationRepository;

use App\Form\UploadType;

use App\Service\UploadService;
use App\Service\ValidationService;
use App\Service\NamingService;


class ValidationController extends AbstractController
{

    private $logger;

    // Repository methods
    private $approbationRepository;
    private $uploadRepository;
    private $userRepository;


    // Services methods
    private $validationService;
    private $uploadService;
    private $namingService;



    public function __construct(

        LoggerInterface                 $logger,

        // Repository methods
        ApprobationRepository           $approbationRepository,
        UploadRepository                $uploadRepository,
        UserRepository                  $userRepository,


        // Services methods
        ValidationService               $validationService,
        UploadService                   $uploadService,
        NamingService                   $namingService,

    ) {
        $this->logger                       = $logger;

        // Variables related to the repositories
        $this->approbationRepository        = $approbationRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->userRepository               = $userRepository;

        // Variables related to the services
        $this->validationService            = $validationService;
        $this->uploadService                = $uploadService;
        $this->namingService                = $namingService;
    }



    // Is not currently in use, but might get useful for the operator side validation.
    #[Route('/validation/{uploadId}', name: 'app_validation')]
    public function validationViewBasePage(
        int $uploadId,
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
        ?int $approbationId = null
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
    public function validationDownloadFile(
        ?int $approbationId = null
    ): Response {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        $validation  = $approbation->getValidation();
        $file        = $validation->getUpload();

        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }



    #[Route('/validation/download/{uploadId}', name: 'app_validation_view_file')]
    public function validationFileView(
        Request $request,
        ?int $uploadId = null,
    ): Response {

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
    public function validationApproval(
        Request $request,
        ?int $approbationId = null
    ): Response {
        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);
        try {
            $response = $this->validationService->validationApproval($approbation, $request);
            if ($response) {
                $this->addFlash('success', 'Le fichier a été validé.');
            } else {
                $this->addFlash('danger', 'Le fichier a été désapprouver.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la validation : ' . $e->getMessage());
            $this->logger->error('$this->validationService->validationApproval error', [$e->getMessage()]);
        }

        return $this->redirectToRoute('app_base');
    }


    #[Route('/validation/disapproved/modifyByUpload/{uploadId}', name: 'app_validation_disapproved_modify_by_upload')]
    public function disapprovedValidationModificationByUpload(
        Request $request,
        ?int $uploadId = null
    ): Response {

        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        $validation = $upload->getValidation();

        $approbations = [];
        $approbations = $validation->getApprobations();

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

            $this->namingService->requestUploadFilenameChecks($request);

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
}
