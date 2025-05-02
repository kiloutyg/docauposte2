<?php

namespace App\Controller\Document;

use App\Entity\Upload;

use App\Form\UploadType;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\UploadService;
use App\Service\ValidationService;
use App\Service\SettingsService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;

/**
 * UploadController
 *
 * This controller is responsible for managing the logic of the upload interface,
 * including file uploads, downloads, modifications, and deletions.
 */
class UploadController extends AbstractController
{
    /**
     * @var ValidationService Service for validating file uploads and modifications
     */
    private $validationService;

    /**
     * @var UploadService Service for handling file upload operations
     */
    private $uploadService;

    /**
     * @var SettingsService Service for accessing application settings
     */
    private $settingsService;

    /**
     * @var EntityManagerFacade Facade for entity management operations
     */
    private $entityManagerFacade;

    /**
     * @var ContentManagerFacade Facade for content management operations
     */
    private $contentManagerFacade;

    /**
     * @var string Error message for non-existent files
     */
    private $absentFileId = 'No File with this ID exist';

    /**
     * Constructor for UploadController
     *
     * Initializes all required services and facades for file management.
     *
     * @param ValidationService $validationService Service for validating file uploads
     * @param UploadService $uploadService Service for handling file uploads
     * @param SettingsService $settingsService Service for accessing application settings
     * @param EntityManagerFacade $entityManagerFacade Facade for entity management operations
     * @param ContentManagerFacade $contentManagerFacade Facade for content management operations
     */
    public function __construct(
        // Services methods
        ValidationService               $validationService,
        UploadService                   $uploadService,
        SettingsService                 $settingsService,
        // Facade classes
        EntityManagerFacade             $entityManagerFacade,
        ContentManagerFacade            $contentManagerFacade,

    ) {
        // Variables related to the services
        $this->validationService            = $validationService;
        $this->uploadService                = $uploadService;
        $this->settingsService              = $settingsService;
        // Variables related to the facades
        $this->entityManagerFacade         = $entityManagerFacade;
        $this->contentManagerFacade        = $contentManagerFacade;
    }

    /**
     * Renders the upload interface
     *
     * This function displays the main upload form where users can upload new files.
     *
     * @return Response The rendered upload interface template
     */
    #[Route('/upload', name: 'app_upload')]
    public function index(): Response
    {
        return $this->render('/services/uploads/upload.html.twig', []);
    }

    /**
     * Renders the uploaded files interface
     *
     * This function displays a list of files that have been uploaded to the system.
     *
     * @return Response The rendered uploaded files template
     */
    #[Route('/uploaded', name: 'app_uploaded_files')]
    public function uploaded_files(): Response
    {
        return $this->render(
            'services/uploads/uploaded.html.twig'
        );
    }

    /**
     * Handles file upload requests
     *
     * Processes file uploads, performs validation checks, and handles potential conflicts.
     * The method extracts file information from the request, validates it, and delegates
     * the actual upload to the UploadService.
     *
     * @param Request $request The HTTP request containing file data and metadata
     * @return Response A redirect response to the originating page with status messages
     */
    #[Route('/uploading', name: 'app_generic_upload_files')]
    public function generic_upload_files(Request $request): Response
    {
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
        // Retrieve the button and the newFilename from the request
        $button      = $request->request->get('button');
        // Find the Button entity in the repository based on its ID
        $buttonEntity = $this->entityManagerFacade->find('button', $button);

        // Filename checks to see if compliant and if a newname has been chosen by user
        if (!$this->contentManagerFacade->filenameChecks($request, $request->request->get('newFilename'))) {
            return $this->redirect($originUrl);
        } else {
            $filename = $this->contentManagerFacade->filenameChecks($request, $request->request->get('newFilename'));
        }

        // Check if the file already exists by comparing the filename and the button
        $conflictFile = '';
        $conflictFile = $this->entityManagerFacade->findOneBy('upload', ['button' => $buttonEntity, 'filename' => $filename]);
        // If the file already exists, return an error message
        if ($conflictFile) {
            $this->addFlash('error', 'Le fichier ' . $filename . ' existe déjà.');
            return $this->redirect($originUrl);
        } else {
            // Use the UploadService to handle file uploads
            $name = $this->uploadService->uploadFiles($request, $buttonEntity, $user, $filename);
            $this->addFlash('success', 'Le document ' . $name . ' a été correctement chargé');
            return $this->redirect($originUrl);
        }
    }

    /**
     * Filters and handles file download requests
     *
     * Routes the download request to the appropriate service method based on the file ID.
     * If the file doesn't exist, redirects with an error message.
     *
     * @param Request $request The HTTP request
     * @param int|null $uploadId The ID of the file to download
     * @return Response The file download response or a redirect with error message
     */
    #[Route('/download/{uploadId}', name: 'app_download_file')]
    public function filterDownloadFile(Request $request, ?int $uploadId = null): Response
    {
        if ($uploadId) {
            return $this->uploadService->filterDownloadFile($uploadId, $request);
        } else {
            $this->addFlash('warning', $this->absentFileId);
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }

    /**
     * Downloads a file using its path
     *
     * Alternative download method that uses the file path instead of streaming.
     * Useful for certain file types or when specific download behavior is needed.
     *
     * @param int $uploadId The ID of the file to download
     * @param Request $request The HTTP request
     * @return Response The file download response or a redirect with error message
     */
    #[Route('/downloadByPath/{uploadId}', name: 'app_download_file_from_path')]
    public function downloadFileFromPath(int $uploadId, Request $request): Response
    {
        if ($uploadId) {
            return $this->uploadService->downloadFileFromPath($uploadId);
        } else {
            $this->addFlash('warning', $this->absentFileId);
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }

    /**
     * Downloads a file that is in validation process
     *
     * Specialized download method for files that are still in the validation process.
     * This may apply different access controls or metadata handling.
     *
     * @param Request $request The HTTP request
     * @param int|null $uploadId The ID of the file to download
     * @return Response The file download response or a redirect with error message
     */
    #[Route('/download/invalidation/{uploadId}', name: 'app_download_invalidation_file')]
    public function downloadInValidationFile(Request $request, ?int $uploadId = null): Response
    {
        if ($uploadId) {
            return $this->uploadService->downloadInValidationFile($uploadId);
        } else {
            $this->addFlash('warning', $this->absentFileId);
            $originUrl = $request->headers->get('referer');
            return $this->redirect($originUrl);
        }
    }

    /**
     * Deletes a file
     *
     * Removes a file from the system and provides user feedback via flash messages.
     * Redirects back to the originating page after deletion.
     *
     * @param Request $request The HTTP request
     * @param int|null $uploadId The ID of the file to delete
     * @return RedirectResponse A redirect to the originating page with status message
     */
    #[Route('/delete/upload/{uploadId}', name: 'app_delete_file')]
    public function deleteFile(Request $request, ?int $uploadId = null): RedirectResponse
    {
        $originUrl = $request->headers->get('Referer');
        $name = $this->entityManagerFacade->deleteFile($uploadId);
        $this->addFlash('success', 'Le fichier  ' . $name . ' a été supprimé.');
        return $this->redirect($originUrl);
    }

    /**
     * Renders the file modification view
     *
     * Displays the form for modifying file metadata and properties.
     * Retrieves the file and its related entities for context in the view.
     *
     * @param Request $request The HTTP request
     * @param int|null $uploadId The ID of the file to modify
     * @return Response The rendered modification view with file context
     */
    #[Route('/modification/view/{uploadId}', name: 'app_modify_file')]
    public function fileModificationView(Request $request, ?int $uploadId = null): Response
    {
        // Retrieve the current upload entity based on the uploadId
        $upload      = $this->entityManagerFacade->find('upload', $uploadId);
        $button      = $upload->getButton();
        $category    = $button->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);

        return $this->render('services/uploads/uploads_modification.html.twig', [
            'form'        => $form->createView(),
            'zone'        => $zone,
            'productLine' => $productLine,
            'category'    => $category,
            'button'      => $button,
            'upload'      => $upload
        ]);
    }

    /**
     * Processes file modification requests
     *
     * Handles the POST request for file modifications, validates the input,
     * and delegates to appropriate methods for form processing.
     * This method separates GET and POST handling to resolve issues with
     * page reloading and comment persistence.
     *
     * @param Request $request The HTTP request containing modification data
     * @param int $uploadId The ID of the file to modify
     * @return Response A redirect to the modification view with status messages
     */
    #[Route('/modification/modifying/{uploadId}', name: 'app_modifying_file')]
    public function modifyingFile(Request $request, int $uploadId): Response
    {
        if (!$request->isMethod('POST')) {
            $this->addFlash('warning', 'Invalid request.');
            return $this->redirectToRoute('app_modify_file', [
                'uploadId' => $uploadId
            ]);
        }
        // Retrieve the current upload entity based on the uploadId
        $upload = $this->entityManagerFacade->find('upload', $uploadId);
        // Check if there is a file to modify
        if (!$upload) {
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirectToRoute('app_modify_file', [
                'uploadId' => $uploadId
            ]);
        }

        // Checking if filename use is compliant
        $this->contentManagerFacade->requestUploadFilenameChecks($request);

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $upload);
        $form->handleRequest($request);
        $this->modifyingFileFormManagement($form, $request, $upload);

        return $this->redirectToRoute('app_modify_file', [
            'uploadId' => $uploadId
        ]);
    }

    /**
     * Handles the form submission for file modification.
     *
     * This method processes the form data for modifying an uploaded file. It extracts
     * boolean values from the request, checks validation requirements, and delegates
     * to appropriate methods based on form validity.
     *
     * @param Form    $form             The form instance containing the modification data
     * @param Request $request          The HTTP request containing form data and files
     * @param Upload  $upload           The Upload entity being modified
     *
     * @return void   This method doesn't return a value but adds flash messages for user feedback
     */
    private function modifyingFileFormManagement(Form $form, Request $request, Upload $upload): void
    {
        $trainingNeeded = filter_var($request->request->get('training-needed'), FILTER_VALIDATE_BOOLEAN);
        $forcedDisplay = filter_var($request->request->get('display-needed'), FILTER_VALIDATE_BOOLEAN);

        $newValidation = filter_var($request->request->get('validatorRequired'), FILTER_VALIDATE_BOOLEAN);

        $neededValidator = $this->settingsService->getSettings()->getValidatorNumber();
        $enoughValidator = $this->validationService->checkNumberOfValidator($request, $neededValidator);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->modFormSubValid($upload, $request, $trainingNeeded, $forcedDisplay, $newValidation, $neededValidator, $enoughValidator);
        } else {
            $this->modFormSubInvalid($form);
        }
    }

    /**
     * Processes valid form submissions for file modifications
     *
     * Handles the business logic for valid form submissions, including validation
     * requirements, comment validation, and file modification through the upload service.
     * Adds appropriate flash messages for user feedback based on the operation result.
     *
     * @param Upload $upload The Upload entity being modified
     * @param Request $request The HTTP request containing form data
     * @param bool $trainingNeeded Whether training is needed for this file
     * @param bool $forcedDisplay Whether display is forced for this file
     * @param bool $newValidation Whether new validation is required
     * @param int $neededValidator The number of validators required by settings
     * @param bool $enoughValidator Whether enough validators have been selected
     * @return void This method doesn't return a value but adds flash messages for user feedback
     */
    private function modFormSubValid(Upload $upload, Request $request, bool $trainingNeeded, bool $forcedDisplay, bool $newValidation, int $neededValidator, bool $enoughValidator = false): void
    {
        try {
            if ($trainingNeeded == null || $forcedDisplay == null) {
                $comment = $request->request->get('modificationComment');
                if ($upload->getFile() && $upload->getValidation() != null && empty($comment) && $request->request->get('modification-outlined' == '')) {
                    $this->addFlash('error', 'Le commentaire est vide. Commenter votre modification est obligatoire.');
                } elseif ($newValidation && !$enoughValidator) {
                    $this->addFlash('error', 'Selectionner au moins ' . $neededValidator . ' validateurs pour valider le fichier.');
                }
            }
            $this->uploadService->modifyFile($upload, $request);
            $this->addFlash('success', 'Le fichier a été modifié.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
    }

    /**
     * Handles invalid form submissions for file modifications
     *
     * Extracts validation errors from the form and adds them as flash messages.
     * If no specific errors are found, displays a generic error message.
     * This provides detailed feedback to users about form validation issues.
     *
     * @param Form $form The form instance containing validation errors
     * @return void This method doesn't return a value but adds flash messages for user feedback
     */
    private function modFormSubInvalid(Form $form): void
    {
        // Extract validation errors and add them to flash messages
        if ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            // If no specific errors were found, show a generic message
            if (count($form->getErrors(true)) === 0) {
                $this->addFlash('error', 'Invalid form. Check the entered data.');
            }
        } else {
            $this->addFlash('error', 'Invalid form. Could not get submitted. Check the entered data.');
        }
    }
}
