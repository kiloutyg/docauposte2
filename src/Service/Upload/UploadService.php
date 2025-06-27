<?php

namespace App\Service\Upload;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\UploadRepository;

use App\Entity\Upload;
use App\Entity\User;

use App\Service\Factory\ServiceFactory;

/**
 * UploadService - Core service for managing document uploads and file operations
 *
 * This service handles all aspects of file uploads in the document management system including:
 * - Processing new file uploads
 * - Managing file downloads based on validation status
 * - Organizing uploads by organizational structure
 * - Handling file validation workflows
 * - Managing file access permissions based on validation status
 *
 * The service implements business rules for file access, ensuring that files
 * are only accessible according to their validation status, training requirements,
 * and forced display settings.
 */
class UploadService extends AbstractController
{

    /**
     * @var LoggerInterface Logger for recording service operations and errors
     */
    private $logger;

    /**
     * @var EntityManagerInterface Doctrine entity manager for database operations
     */
    private $em;

    /**
     * @var UploadRepository Repository for Upload entity operations
     */
    private $uploadRepository;

    private $validationService;

    private $oldUploadService;

    private $settingsService;

    private $folderService;

    private $fileTypeService;

    /**
     * @var ServiceFactory Service for creating and managing service instances
     */
    private $serviceFactory;

    /**
     * Constructor for the UploadService class.
     *
     * Initializes the service with necessary dependencies for managing file uploads,
     * validation, storage, and related operations.
     *
     * @param EntityManagerInterface $em                The entity manager for database operations
     * @param UploadRepository       $uploadRepository  Repository for Upload entity operations
     * @param ServiceFactory         $serviceFactory    Service for creating and managing service instances
     */

    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,
        UploadRepository                $uploadRepository,
        ServiceFactory                  $serviceFactory
    ) {
        $this->logger                   = $logger;
        $this->em                       = $em;

        $this->uploadRepository         = $uploadRepository;
        $this->serviceFactory            = $serviceFactory;

        $this->validationService        = $this->serviceFactory->getService(className: 'Validation\\Validation');
        $this->oldUploadService         = $this->serviceFactory->getService(className: 'Upload\\OldUpload');
        $this->settingsService          = $this->serviceFactory->getService(className: 'Settings');
        $this->folderService            = $this->serviceFactory->getService(className: 'Folder');
        $this->fileTypeService          = $this->serviceFactory->getService(className: 'Upload\\FileType');
    }




    // This function is responsible for the logic of uploading the uploads files
    /**
     * This function is responsible for uploading files and managing their related data.
     *
     * @param Request $request The request object containing the uploaded files.
     * @param mixed   $button  The button related to the uploaded files.
     * @param User    $user    The user who uploaded the files.
     * @param string  $filename The name of the uploaded file.
     *
     * @return string The name of the last uploaded file.
     */
    public function uploadFiles(Request $request, $button, User $user, $filename)
    {
        // Get all the files from the request
        $files = $request->files->all();

        // Iterate over each file
        foreach ($files as $file) {

            $this->fileTypeService->checkFileType($file);

            // Construct the full path of the file
            $folderPath = $this->folderService->pathFindingDoc($button->getName());
            $path = $folderPath . '/' . $filename;
            // Move the file to the specified folder
            $file->move($folderPath . '/', $filename);

            // Create a new Upload object
            $upload = new Upload();

            // Set the file property using the path
            $upload->setFile(new File($path));
            // Set the filename property
            $upload->setFilename($filename);
            // Set the path property
            $upload->setPath($path);
            // Set the button property
            $upload->setButton($button);
            // Set the uploader property
            $upload->setUploader($user);
            // Set the uploadedAt property to the current date and time
            $upload->setUploadedAt(new \DateTime());
            // Set the revision property
            $upload->setRevision(1);
            // Persist the upload object
            $this->em->persist($upload);
            // Set training and validation related stuff
            $this->uploadTrainingValidationChecker($request, $upload);
        }

        // Save the changes to the database
        $this->em->flush();

        // Return the name of the last uploaded file
        return $filename;
    }



    /**
     * This function checks and sets the training, display, and validation properties of an Upload entity based on the request parameters.
     *
     * @param Request $request The request object containing the parameters.
     * @param Upload  $upload  The Upload entity to be updated.
     *
     * @return void
     */
    public function uploadTrainingValidationChecker(Request $request, Upload $upload): void
    {
        // Check if the file need to be validated or not, by checking if there is a validator_department or a validator_user string in the request
        if ($request->request->get('validatorRequired') == 'true') {
            foreach ($request->request->keys() as $key) {
                if (strpos($key, 'validator_user') !== false) {
                    $validated = null;
                }
            }
        } else {
            $validated = true;
        }

        if ($request->request->get('training-needed') === 'true') {
            $trainingNeeded = true;
        } else {
            $trainingNeeded = false;
        }

        if ($request->request->get('display-needed') === 'true') {
            $displayNeeded = true;
        } else {
            $displayNeeded = false;
        }

        // Set the training property
        $upload->setTraining($trainingNeeded);
        // Set the display property
        $upload->setForcedDisplay($displayNeeded);
        // Set the validated boolean property
        $upload->setValidated($validated);

        if ($validated === null) {
            $this->validationService->createValidation($upload, $request);
        }
    }






    /**
     * Groups uploads into hierarchical arrays based on their organizational structure.
     *
     * This function organizes uploads into nested arrays based on their zone, product line,
     * category, and button. It creates two separate groupings: one for all uploads and
     * another specifically for uploads that have validation data.
     *
     * @param array $uploads An array of Upload entities to be grouped
     *
     * @return array An array containing two elements:
     *               - First element: Hierarchical array of all uploads grouped by organizational structure
     *               - Second element: Hierarchical array of only validated uploads grouped by organizational structure
     */
    public function groupAllUploads($uploads): array
    {
        $groupedUploads = [];
        $groupedValidatedUploads = [];
    
        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {
            $zoneName        = $upload->getButton()->getCategory()->getProductLine()->getZone()->getName();
            $productLineName = $upload->getButton()->getCategory()->getProductLine()->getName();
            $categoryName    = $upload->getButton()->getCategory()->getName();
            $buttonName      = $upload->getButton()->getName();
    
            $groupedUploads = $this->groupingNonValidatedUpload($groupedUploads, $upload, $zoneName, $productLineName, $categoryName, $buttonName);
    
            if ($upload->getValidation()) {
                $groupedValidatedUploads = $this->groupingValidatedUpload($groupedValidatedUploads, $upload, $zoneName, $productLineName, $categoryName, $buttonName);
            }
        }
    
        return [$groupedUploads, $groupedValidatedUploads];
    }




    /**
     * Groups a non-validated upload into a hierarchical array structure based on organizational elements.
     *
     * This function organizes uploads into a nested array structure according to their zone,
     * product line, category, and button. It ensures that all necessary array levels exist
     * before adding the upload to the appropriate location in the hierarchy.
     *
     * @param array  $groupedUploads   The existing hierarchical array of grouped uploads
     * @param Upload $upload           The upload entity to be added to the grouped structure
     * @param string $zoneName         The name of the zone associated with the upload
     * @param string $productLineName  The name of the product line associated with the upload
     * @param string $categoryName     The name of the category associated with the upload
     * @param string $buttonName       The name of the button associated with the upload
     *
     * @return array The updated hierarchical array with the new upload added
     */
    public function groupingNonValidatedUpload(
        array  $groupedUploads,
        Upload $upload,
        string $zoneName,
        string $productLineName,
        string $categoryName,
        string $buttonName
    ): array {

        if (!isset($groupedUploads[$zoneName])) {
            $groupedUploads[$zoneName] = [];
        }
        if (!isset($groupedUploads[$zoneName][$productLineName])) {
            $groupedUploads[$zoneName][$productLineName] = [];
        }
        if (!isset($groupedUploads[$zoneName][$productLineName][$categoryName])) {
            $groupedUploads[$zoneName][$productLineName][$categoryName] = [];
        }
        if (!isset($groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName])) {
            $groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName] = [];
        }
        $groupedUploads[$zoneName][$productLineName][$categoryName][$buttonName][] = $upload;

        return $groupedUploads;
    }



    
    /**
     * Groups a validated upload into a hierarchical array structure based on organizational elements.
     *
     * This function organizes validated uploads into a nested array structure according to their zone,
     * product line, category, and button. It ensures that all necessary array levels exist
     * before adding the upload to the appropriate location in the hierarchy.
     *
     * @param array  $groupedValidatedUploads The existing hierarchical array of grouped validated uploads
     * @param Upload $upload                  The validated upload entity to be added to the grouped structure
     * @param string $zoneName                The name of the zone associated with the upload
     * @param string $productLineName         The name of the product line associated with the upload
     * @param string $categoryName            The name of the category associated with the upload
     * @param string $buttonName              The name of the button associated with the upload
     *
     * @return array The updated hierarchical array with the new validated upload added
     */
    public function groupingValidatedUpload(
        array $groupedValidatedUploads,
        Upload $upload,
        string $zoneName,
        string $productLineName,
        string $categoryName,
        string $buttonName
    ): array {

        if (!isset($groupedValidatedUploads[$zoneName])) {
            $groupedValidatedUploads[$zoneName] = [];
        }
        if (!isset($groupedValidatedUploads[$zoneName][$productLineName])) {
            $groupedValidatedUploads[$zoneName][$productLineName] = [];
        }
        if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName])) {
            $groupedValidatedUploads[$zoneName][$productLineName][$categoryName] = [];
        }
        if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName])) {
            $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName] = [];
        }
        $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName][] = $upload;

        return $groupedValidatedUploads;
    }


    /**
     * Filters and processes the download request for a specific upload file based on its validation status and settings.
     *
     * @param int         $uploadId The ID of the upload file to be downloaded.
     * @param Request     $request  The request object containing the origin URL.
     *
     * @return Response The response object representing the download file or a redirect to a validation page.
     *
     */
    public function filterDownloadFile(int $uploadId, Request $request): Response
    {
        $upload = $this->uploadRepository->find($uploadId);
        if (!$upload) {
            $this->addFlash('warning', 'Aucun document trouvé avec l\'ID ' . $uploadId);
            return $this->redirect($request->headers->get('referer'));
        }
        $settings = $this->settingsService->getSettings();
        if (!($settings->isUploadValidation() || $settings->IsTraining())) {
            return $this->downloadFileFromMethods($upload->getPath());
        }
        return $this->filterDownloadFileResponse($upload, $request);
    }




    /**
     * Filters and processes the download request for a specific upload file based on its validation status and settings.
     *
     * @param Upload $upload The upload file to be downloaded.
     * @param Request $request The request object containing the origin URL.
     *
     * @return Response The response object representing the download file or a redirect to a validation page.
     *
     */
    public function filterDownloadFileResponse(Upload $upload, Request $request): Response
    {
        $isValidated        = $upload->isValidated();
        $isForcedDisplay    = (bool)$upload->isForcedDisplay();
        $isTraining         = (bool)$upload->isTraining();
        $hasOldUpload       = $upload->getOldUpload() !== null;
        $originUrl          = $request->headers->get('Referer');

        if ($isValidated === true) {
            return $this->filterDownloadFileIsValidated(
                isTraining: $isTraining,
                upload: $upload
            );
        } elseif ($isValidated === false) {
            return $this->filterDownloadFileIsRefused(
                isTraining: $isTraining,
                hasOldUpload: $hasOldUpload,
                upload: $upload,
                originUrl: $originUrl
            );
        } else {
            return $this->filterDownloadFileIsBeingValidated(
                isTraining: $isTraining,
                hasOldUpload: $hasOldUpload,
                upload: $upload,
                isForcedDisplay: $isForcedDisplay,
                originUrl: $originUrl
            );
        }
    }



    /**
     * Filters and processes the download request for a validated upload file based on its training status.
     *
     * @param bool     $isTraining Indicates whether the upload file is related to training.
     * @param Upload   $upload     The validated upload file to be downloaded.
     *
     * @return Response The response object representing the download file or a redirect to a training page.
     */
    public function filterDownloadFileIsValidated(bool $isTraining, Upload $upload): Response
    {
        if ($isTraining) {
            $response = $this->redirectToRoute('app_training_front_by_upload', [
                'uploadId' => $upload->getId()
            ]);
        } else {
            $response = $this->downloadFileFromMethods($upload->getPath());
        }
        return $response;
    }




    /**
     * Filters and processes the download request for a refused upload file based on its training status and old upload existence.
     *
     * @param bool     $isTraining     Indicates whether the upload file is related to training.
     * @param bool     $hasOldUpload   Indicates whether the upload file has an old upload.
     * @param Upload   $upload         The refused upload file to be downloaded.
     * @param string   $originUrl      The origin URL of the download request.
     *
     * @return Response The response object representing the download file or a redirect to a validation or training page.
     */
    public function filterDownloadFileIsRefused(bool $isTraining, bool $hasOldUpload, Upload $upload, string $originUrl): Response
    {
        if ($isTraining) {
            $response = $this->redirectToRoute('app_training_front_by_validation', [
                'validationId' => $upload->getValidation()->getId()
            ]);
        } elseif ($hasOldUpload) {
            $response = $this->oldUploadService->manageOldUploadDisplay($upload);
        } else {
            $response = $this->redirect(url: $originUrl, status: 307);
            $this->addFlash(
                'Danger',
                'Le fichier a été refusé par les validateurs et son affichage n\'est pas forcé. Contacter votre responsable pour plus d\'informations.'
            );
        }
        return $response;
    }




    /**
     * Filters and processes the download request for a file in validation status based on its training status, old upload existence, and forced display.
     *
     * @param bool     $isTraining     Indicates whether the upload file is related to training.
     * @param bool     $hasOldUpload   Indicates whether the upload file has an old upload.
     * @param Upload   $upload         The file in validation to be downloaded.
     * @param bool     $isForcedDisplay Indicates whether the upload file's display is forced.
     * @param string   $originUrl      The origin URL of the download request.
     *
     * @return Response The response object representing the download file or a redirect to a training or validation page.
     */
    public function filterDownloadFileIsBeingValidated(bool $isTraining, bool $hasOldUpload, Upload $upload, bool $isForcedDisplay, string $originUrl): Response
    {
        if (!$isForcedDisplay) {
            if ($hasOldUpload) {
                $this->logger->debug('OldUploadService: filterDownloadFileIsBeingValidated: Old upload display');
                $response =  $this->oldUploadService->manageOldUploadDisplay($upload);
            } else {
                $response = $this->redirect($originUrl);
                $this->addFlash(
                    'Danger',
                    'Le fichier est en cours de validation, son affichage n\'est pas forcé et il ne dispose pas d\' une ancienne version. Contacter votre responsable pour plus d\'informations.'
                );
            }
        } else {
            if ($isTraining) {
                $response = $this->redirectToRoute('app_training_front_by_validation', [
                    'validationId' => $upload->getValidation()->getId()
                ]);
            } else {
                $response = $this->downloadFileFromPath($upload->getId());
            }
        }
        return $response;
    }





    /**
     * Downloads a file from the specified path and returns a Response object.
     *
     * This function creates a File object from the given path, then uses the Symfony HttpFoundation's file() function
     * to create a Response object with the file content. The file is set to be displayed inline in the browser.
     *
     * @param string $path The path to the file to be downloaded.
     *
     * @return Response The Response object containing the file content.
     */
    public function downloadFileFromMethods(string $path): Response
    {
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }




    /**
     * Downloads a file from the specified upload ID and returns a Response object.
     *
     * This function retrieves the file associated with the given upload ID from the database,
     * determines the file path using the determineFilePath() method, and then calls the
     * downloadFileFromMethods() method to create a Response object with the file content.
     *
     * @param int $uploadId The ID of the upload for which the file needs to be downloaded.
     *
     * @return Response The Response object containing the file content.
     */
    public function downloadFileFromPath(int $uploadId)
    {
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        $path = $this->determineFilePath($file);
        return $this->downloadFileFromMethods($path);
    }





    // Private method to determine the file path
    /**
     * Determines the file path based on the validation status and forced display settings of the upload.
     *
     * @param mixed $file The upload object for which the file path needs to be determined.
     *
     * @return string The determined file path.
     *
     */
    private function determineFilePath($file): string
    {
        if (!$file->isValidated()) {
            if ($file->isForcedDisplay() === true || $file->isForcedDisplay() === null) {
                return $file->getPath();
            } elseif ($file->getOldUpload() != null) {
                $oldUpload = $file->getOldUpload();
                return $oldUpload->getPath();
            }
        }
        return $file->getPath();
    }





    // create a route to download a file in more simple terms to display the file
    /**
     * This function is responsible for downloading a file related to a validation process.
     *
     * @param int|null $uploadId The ID of the upload file to be downloaded. If null, the function will use the last uploaded file.
     *
     * @return \Symfony\Component\HttpFoundation\Response The response object containing the file content.
     *
     * @throws \Exception If the upload ID is not provided and no file is found in the database.
     */
    public function downloadInValidationFile(?int $uploadId = null)
    {
        // Retrieve the origin URL
        $file = $this->uploadRepository->findOneBy(['id' => $uploadId]);

        if (!$file) {
            throw $this->createNotFoundException('No file found with the provided upload ID.');
        }

        $path = $file->getPath();
        $file = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
