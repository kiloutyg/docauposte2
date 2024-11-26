<?php

namespace App\Controller;

// use \Psr\Log\LoggerInterface;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\UploadType;


use App\Repository\UploadRepository;
use App\Repository\ButtonRepository;

use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\ValidationService;
use App\Service\SettingsService;

// This controlle is responsible for managing the logic of the upload interface
#[Route('/', name: 'app_')]
class UploadController extends AbstractController
{

    private $security;
    // private $logger;
    private $authChecker;

    // Repository methods
    private $buttonRepository;
    private $uploadRepository;


    // Services methods
    private $validationService;
    private $entitydeletionService;
    private $uploadService;
    private $settingsService;




    public function __construct(

        Security                        $security,
        AuthorizationCheckerInterface   $authChecker,

        // Repository methods
        ButtonRepository                $buttonRepository,
        UploadRepository                $uploadRepository,


        // Services methods
        ValidationService               $validationService,
        EntityDeletionService           $entitydeletionService,
        UploadService                   $uploadService,
        SettingsService                 $settingsService

    ) {
        $this->security                     = $security;
        // $this->logger                       = $logger;

        $this->authChecker                  = $authChecker;

        // Variables related to the repositories
        $this->uploadRepository             = $uploadRepository;
        $this->buttonRepository             = $buttonRepository;

        // Variables related to the services
        $this->validationService            = $validationService;
        $this->uploadService                = $uploadService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->settingsService              = $settingsService;
    }




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






    // Create a route to upload a file, and pass the request to the UploadService to handle the file upload
    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(Request $request): Response
    {
        // $this->logger->info('fullrequest', ['request' => $request]);

        // Get the URL of the page from which the request originated
        $originUrl = $request->headers->get('referer');

        // Check if the URL contains the word "button" to bypass issue when uploading stuff directly from a button page
        if (strpos($originUrl, 'button') !== false) {    // Find the position of the word "button"
            $buttonPos = strpos($originUrl, 'button');

            // Find the position of the last '/' before "button"
            $slashPos = strrpos(substr($originUrl, 0, $buttonPos), '/');

            // Truncate the string to remove everything after the last '/'
            $originUrl = substr($originUrl, 0, $slashPos);
        }
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
            // $this->logger->info('originURL', ['originURL' => $originUrl]);
            return $this->redirect($originUrl);
        }
    }





    // create a route to redirect to the correct views of a file
    #[Route('/download/{uploadId}', name: 'download_file')]
    public function filterDownloadFile(int $uploadId = null, Request $request): Response
    {
        if ($uploadId) {
            return $this->uploadService->filterDownloadFile($uploadId, $request);
        } else {
            $this->addFlash('warning', 'No File with ID exist');
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }






    // create a route to redirect to the correct views of a file
    #[Route('/downloadByPath/{uploadId}', name: 'download_file_from_path')]
    public function downloadFileFromPath(int $uploadId, Request $request): Response
    {
        if ($uploadId) {
            return $this->uploadService->downloadFileFromPath($uploadId);
        } else {
            $this->addFlash('warning', 'No File with ID exist');
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }








    // create a route to download a file in more simple terms to display the file
    #[Route('/download/invalidation/{uploadId}', name: 'download_invalidation_file')]
    public function downloadInValidationFile(int $uploadId = null, Request $request): Response
    {
        if ($uploadId) {
            return $this->uploadService->downloadInValidationFile($uploadId);
        } else {
            $this->addFlash('warning', 'No File with ID exist');
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }






    // create a route to delete a file
    #[Route('/delete/upload/{uploadId}', name: 'delete_file')]

    public function deleteFile(int $uploadId = null, Request $request): RedirectResponse
    {
        $originUrl = $request->headers->get('Referer');
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        // Check if the user is the creator of the upload or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN") || $this->getUser() === $upload->getUploader() || $upload->getUploader() === null) {
            // Use the UploadService to handle file deletion
            $name = $this->entitydeletionService->deleteFile($uploadId);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce document.');
            return $this->redirect($originUrl);
        }

        $this->addFlash('success', 'Le fichier  ' . $name . ' a été supprimé.');
        return $this->redirect($originUrl);
    }






    // create a route to modify a file and or display the modification page
    #[Route('/modification/view/{uploadId}', name: 'modify_file')]
    public function fileModificationView(int $uploadId = null, Request $request,): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload      = $this->uploadRepository->find($uploadId);
        $button      = $upload->getButton();
        $category    = $button->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        $currentUser = $this->security->getUser();
        $uploader = $upload->getUploader();
        // If it's a GET request, render the form
        if ($request->isMethod('GET') && ($currentUser === $uploader || $uploader === null || $this->authChecker->isGranted("ROLE_LINE_ADMIN"))) {
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
        // $this->logger->info('fullrequest', ['request' => $request->request->all()]);
        $originUrl = $request->headers->get('referer');

        if (!$request->isMethod('POST')) {
            $this->addFlash('warning', 'Invalid request.');
            return $this->redirectToRoute('app_base');
        };

        // Retrieve the current upload entity based on the uploadId
        $upload = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        // Check if there is a file to modify
        if (!$upload) {
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirect($originUrl);
        }

        $categoryId  = $upload->getButton()->getCategory()->getId();
        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        $trainingNeeded = filter_var($request->request->get('training-needed'), FILTER_VALIDATE_BOOLEAN);
        $forcedDisplay = filter_var($request->request->get('display-needed'), FILTER_VALIDATE_BOOLEAN);
        $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);

        $neededValidator = $this->settingsService->getSettings()->getValidatorNumber();
        $enoughValidator = $this->validationService->checkNumberOfValidator($request, $neededValidator);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                if ($trainingNeeded == null || $forcedDisplay == null) {
                    $comment = $request->request->get('modificationComment');
                    if ($upload->getFile() && $upload->getValidation() != null && empty($comment) && $request->request->get('modification-outlined' == '')) {
                        $this->addFlash('error', 'Le commentaire est vide. Commenter votre modification est obligatoire.');
                        return $this->redirectToRoute('app_category_admin', [
                            'categoryId' => $categoryId
                        ]);
                    } elseif ($newValidation && !$enoughValidator) {
                        $this->addFlash('error', 'Selectionner au moins ' . $neededValidator . ' validateurs pour valider le fichier.');
                        return $this->redirectToRoute('app_category_admin', [
                            'categoryId' => $categoryId
                        ]);
                    }
                }
                $this->uploadService->modifyFile($upload, $request);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirectToRoute('app_category_admin', [
                    'categoryId' => $categoryId
                ]);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_category_admin', [
                    'categoryId' => $categoryId
                ]);
            }
        } else {
            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirectToRoute('app_category_admin', [
                'categoryId' => $categoryId
            ]);
        }
    }
}
