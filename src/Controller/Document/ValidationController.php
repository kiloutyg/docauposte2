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

use App\Service\Upload\UploadModificationService;
use App\Service\Validation\ValidationService;
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
    private $uploadModificationService;
    private $namingService;



    public function __construct(

        LoggerInterface                 $logger,

        // Repository methods
        ApprobationRepository           $approbationRepository,
        UploadRepository                $uploadRepository,
        UserRepository                  $userRepository,


        // Services methods
        ValidationService               $validationService,
        UploadModificationService       $uploadModificationService,
        NamingService                   $namingService,

    ) {
        $this->logger                       = $logger;

        // Variables related to the repositories
        $this->approbationRepository        = $approbationRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->userRepository               = $userRepository;

        // Variables related to the services
        $this->validationService            = $validationService;
        $this->uploadModificationService    = $uploadModificationService;
        $this->namingService                = $namingService;
    }



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



    /**
     * Displays the approbation page for a validation request.
     *
     * This function retrieves an approbation record by its ID and renders a page
     * where users can approve or disapprove a document. If the approbation has
     * already been responded to, the user is redirected to the base page with
     * an error message.
     *
     * @param int|null $approbationId The ID of the approbation record to display,
     *                                null if not provided
     *
     * @return Response A Symfony Response object that either renders the approbation
     *                  page or redirects to the base page if already processed
     */
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



    /**
     * Downloads a file associated with a specific approbation request.
     *
     * This function retrieves an approbation record by its ID, gets the associated validation
     * and upload file, and serves the file for inline viewing in the browser.
     *
     * @param int|null $approbationId The ID of the approbation record to retrieve the file from,
     *                                null if not provided
     *
     * @return Response A Symfony Response object that serves the requested file
     *                  for inline viewing in the browser
     */
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



    /**
     * Handles the viewing of a file that is pending validation.
     *
     * This function retrieves a file based on its upload ID and displays it to the user.
     * If the file has already been validated, the user is redirected back to the previous page
     * with an error message. Otherwise, the file is served for inline viewing in the browser.
     *
     * @param Request $request The HTTP request object containing headers and other request data
     * @param int|null $uploadId The ID of the upload to be viewed, null if not provided
     *
     * @return Response A Symfony Response object that either redirects the user or
     *                  serves the requested file for inline viewing
     */
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



    /**
     * Processes the approval or disapproval of a document validation request.
     *
     * This function handles the validation decision for a document based on an approbation request.
     * It retrieves the approbation record, processes the approval/disapproval action through the
     * validation service, and provides appropriate feedback to the user via flash messages.
     *
     * @param Request $request The HTTP request object containing validation decision data
     * @param int|null $approbationId The ID of the approbation record to process, null if not provided
     *
     * @return Response A Symfony Response object that redirects to the base application page
     *                  after processing the validation decision
     */
    #[Route('/validation/approval/{approbationId}', name: 'app_validation_approval')]
    public function validationApproval(
        Request $request,
        ?int $approbationId = null
    ): Response {
        $this->logger->info('ValidationController::validationApproval - approbation ID: ' . $approbationId);
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


    /**
     * Handles the modification of a disapproved file upload.
     *
     * This function allows users to modify a file that was previously disapproved during validation.
     * It retrieves the upload, creates a form for modification, processes the form submission,
     * and handles the file modification process.
     *
     * @param Request $request The HTTP request object containing form data and headers
     * @param int|null $uploadId The ID of the upload to be modified, null if not provided
     *
     * @return Response A Symfony Response object that either renders the modification form
     *                  or redirects to another page based on the operation's outcome
     */
    #[Route('/validation/disapproved/modifyByUpload/{uploadId}', name: 'app_validation_disapproved_modify_by_upload')]
    public function disapprovedValidationModificationByUpload(
        Request $request,
        ?int $uploadId = null
    ): Response {
        $this->logger->info('ValidationController::disapprovedValidationModificationByUpload - upload ID: ' . $uploadId);

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

        if ($request->isMethod(method: 'POST')) {
            $this->logger->info('ValidationController::disapprovedValidationModificationByUpload - form submitted full request before name checking: ', $request->request->all());
            $this->namingService->requestUploadFilenameChecks($request);
            $this->logger->info('ValidationController::disapprovedValidationModificationByUpload - form submitted full request after name checking: ', $request->request->all());

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->uploadModificationService->modifyDisapprovedFile($upload, $user, $request);
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
