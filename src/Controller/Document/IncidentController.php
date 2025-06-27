<?php

namespace App\Controller\Document;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\Routing\Annotation\Route;


use App\Entity\IncidentCategory;

use App\Form\IncidentType;

use App\Service\Incident\IncidentService;
use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;

// This controller manage the logics of the incidents interface, it is responsible for rendering the incidents interface.
// It is also responsible for creating, modifying, deleting incidents and incident categories.
#[Route('/', name: 'app_')]
class IncidentController extends AbstractController
{
    private $logger;

    private $incidentService;
    private $entityManagerFacade;
    private $contentManagerFacade;

    public function __construct(
        LoggerInterface                 $logger,

        // Services methods
        IncidentService                 $incidentService,
        EntityManagerFacade             $entityManagerFacade,
        ContentManagerFacade            $contentManagerFacade,

    ) {
        $this->logger                       = $logger;

        // Variables related to the services
        $this->incidentService              = $incidentService;
        $this->contentManagerFacade         = $contentManagerFacade;
        $this->entityManagerFacade          = $entityManagerFacade;
    }




    // Render the Incident management view in any role level admin page
    /**
     * Renders the incident management view for admin users.
     *
     * This method retrieves all incidents from the database, groups them using the incident service,
     * and fetches all incident categories to display in the admin interface.
     *
     * @return Response A rendered Symfony Response object containing the incident management view
     */
    #[Route('/admin/incidentmanagementview', name: 'incident_management_view')]
    public function incidentManagementView(): Response
    {

        $incidents = $this->entityManagerFacade->getIncidents();

        $groupIncidents = $this->incidentService->groupIncidents($incidents);
        $incidentCategories = $this->entityManagerFacade->getIncidentCategories();

        return $this->render(
            'services/incident/incident.html.twig',
            [
                'groupincidents'            => $groupIncidents,
                'incidentCategories'        => $incidentCategories,
            ]
        );
    }




    // Render the incidents page and filter the incidents by productLine and sort them by id ascending to display them in the right order
    /**
     * Displays an incident for a specific product line.
     *
     * This method retrieves and displays an incident associated with a product line.
     * If an incident is found, it renders the incident view template with incident details.
     * If no incident is found, it renders the product line template instead.
     *
     * @param int|null $productLineId The ID of the product line to which the incident belongs, null if not specified
     * @param int|null $incidentId The ID of the incident to display, null if not specified
     *
     * @return Response A rendered Symfony Response object containing either the incident view or product line view
     */
    #[Route('/productLine/{productLineId}/incident/{incidentId}', name: 'mandatory_incident')]
    public function mandatoryIncident(?int $productLineId = null, ?int $incidentId = null): Response
    {

        $response = $this->incidentService->displayIncident($productLineId, $incidentId);
        $incident       = $response[0];
        $productLine    = $response[1];
        $nextIncident   = $response[2];

        // If there is an incident we render the incidents page with the incident data and the next incident id to redirect to the next incident page
        if ($incident) {
            return $this->render(
                '/services/incident/incident_view.html.twig',
                [
                    'incidentId'        => $incident ? $incident->getId() : null,
                    'incident'          => $incident,
                    'incidentCategory'  => $incident ? $incident->getIncidentCategory() : null,
                    'productLineId'     => $productLine->getId(),
                    'nextIncidentId'    => $nextIncident ? $nextIncident->getId() : null
                ]
            );
        } else {
            return $this->render(
                'productLine.html.twig',
                [
                    'productLine' => $productLine,
                    'categories' => $productLine->getCategories(),
                ]
            );
        }
    }




    // Logic to create a new IncidentCategory and display a message
    /**
     * Creates a new incident category based on the provided request data.
     *
     * This method processes a JSON request containing an incident category name,
     * validates that the name is not empty and doesn't already exist in the database,
     * and creates a new IncidentCategory entity if validation passes.
     *
     * @param Request $request The HTTP request containing JSON data with the incident category name
     *                         in the 'incident_incidentCategory_name' field
     *
     * @return JsonResponse A JSON response indicating success or failure:
     *                      - On success: ['success' => true, 'message' => 'Le type d\'incident a été créé']
     *                      - On failure (empty name): ['success' => false, 'message' => 'Le nom du category d\'incident ne peut pas être vide']
     *                      - On failure (duplicate): ['success' => false, 'message' => 'Ce category d\'incident existe déjà']
     */
    #[Route('/incident/incident_incidentCategory_creation', name: 'incident_incidentCategory_creation')]
    public function incidentCategoryCreation(Request $request): JsonResponse
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        // Get the name of the incident category
        $incidentCategoryName = $data['incident_incidentCategory_name'] ?? null;

        // Get the existing incident category name
        $existingIncidentCategory = $this->entityManagerFacade->findOneBy('category', ['name' => $incidentCategoryName]);

        // Check if the incident category name is empty or if the incident category already exists
        if (empty($incidentCategoryName)) {
            return new JsonResponse(['success' => false, 'message' => 'Le nom du category d\'incident ne peut pas être vide']);
        }
        if ($existingIncidentCategory) {
            return new JsonResponse(['success' => false, 'message' => 'Ce category d\'incident existe déjà']);
            // If the incident category does not exist, we create it
        } else {
            $incidentCategory = new IncidentCategory();
            $incidentCategory->setName($incidentCategoryName);

            $em = $this->entityManagerFacade->getEntityManager();
            $em->persist($incidentCategory);
            $em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Le type d\'incident a été créé']);
        }
    }




    // Create a route for incidentCategory deletion. It depends on the entitydeletionService.
    /**
     * Deletes an incident category from the system.
     *
     * This method attempts to delete an incident category based on the provided ID.
     * If successful, it adds a success flash message and redirects to the origin URL.
     * If the category doesn't exist or deletion fails, it logs an error and adds a danger flash message.
     *
     * @param int $incidentCategoryId The ID of the incident category to delete
     * @param Request $request The current HTTP request object, used for redirection
     *
     * @return Response A redirect response to the origin URL with appropriate flash messages
     */
    #[Route('/incident/delete/incident_incidentCategory_deletion/{incidentCategoryId}', name: 'incident_incidentCategory_deletion')]
    public function incidentCategoryDeletion(int $incidentCategoryId, Request $request): Response
    {
        $entityType = "incidentCategory";
        $entity = $this->entityManagerFacade->deleteEntity($entityType, $incidentCategoryId);

        if ($entity) {
            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToOriginUrl($request);
        } else {
            $this->logger->error('Erreur lors de la suppression du ' . $entityType);
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToOriginUrl($request);
        }
    }




    // Create a route to upload an incident file. It depends on the IncidentService.
    /**
     * Handles the upload of incident files.
     *
     * This method processes POST requests containing incident file uploads.
     * It delegates the actual file processing to the IncidentService and
     * provides appropriate feedback to the user through flash messages.
     *
     * @param Request $request The HTTP request object containing the uploaded file data
     *                         and other form information
     *
     * @return Response A redirect response to the origin URL with appropriate flash messages
     *                  indicating success or failure of the upload operation
     */
    #[Route('/incident/incident_uploading', name: 'generic_upload_incident_files')]
    public function genericUploadOfIncidentFiles(Request $request): Response
    {

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Use the IncidentService to handle the upload of the Incidents files
            try {
                $name = $this->incidentService->uploadIncidentFiles($request);
                $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');
                return $this->redirectToOriginUrl($request);
            } catch (\Exception $e) {
                $this->logger->error('Error during file upload', [$e->getMessage()]);
                $this->addFlash('danger', 'Les documents n\'ont pas pu être chargés. Erreur : ' . $e->getMessage());
                return $this->redirectToOriginUrl($request);
            }
        } else {
            // Show an error message if the form is not submitted
            $this->logger->info('Le fichier n\'a pas été poster correctement.');
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirectToOriginUrl($request);
        }
    }




    // Create a route to download a file, in more simple terms, to display a file.
    /**
     * Downloads or displays an incident file in the browser.
     *
     * This method retrieves an incident file based on the provided ID,
     * creates a File object from its path, and returns it as an inline
     * response to be displayed directly in the browser rather than
     * being downloaded as an attachment.
     *
     * @param int|null $incidentId The ID of the incident file to download/display,
     *                             null if not specified
     *
     * @return Response A Symfony Response object containing the file
     *                  with headers set to display the file inline in the browser
     */
    #[Route('/download_incident/{incidentId}', name: 'incident_download_file')]
    public function download_file(?int $incidentId = null): Response
    {
        $file       = $this->entityManagerFacade->find('incident', $incidentId);
        $path       = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }





    // Create a route to delete a file
    /**
     * Deletes an incident file from the system.
     *
     * This method retrieves an incident entity based on the provided ID,
     * logs the deletion attempt, and uses the incident service to perform
     * the actual file deletion. On success, it adds a flash message with
     * the deleted file name. On failure, it logs the error and adds an
     * error flash message.
     *
     * @param int $incidentId The ID of the incident file to delete
     * @param Request $request The HTTP request object used for redirection
     *
     * @return Response A redirect response to the origin URL with appropriate
     *                  flash messages indicating success or failure
     */
    #[Route('/delete/incident/{incidentId}', name: 'incident_delete_file')]
    public function delete_file(int $incidentId, Request $request): Response
    {
        $incidentEntity = $this->entityManagerFacade->find('incident', $incidentId);
        $this->logger->debug('Deleting file: ' . $incidentEntity->getPath());
        try {
            $name = $this->incidentService->deleteIncidentFile($incidentEntity);
            $this->addFlash('success', 'File ' . $name . ' deleted');
            return $this->redirectToOriginUrl(request: $request);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting file', [$e->getMessage()]);
            $this->addFlash('danger', 'Error deleting file. Error: ' . $e->getMessage());
            return $this->redirectToOriginUrl(request: $request);
        }
    }




    // Create a route to modify a file and or display the modification page
    /**
     * Handles the modification of an incident file.
     *
     * This method processes both GET and POST requests for incident file modification.
     * For GET requests, it displays a form to modify the incident details.
     * For POST requests, it validates the submitted data, processes the modification,
     * and provides appropriate feedback through flash messages.
     *
     * @param Request $request The HTTP request object containing form data for POST requests
     * @param int $incidentId The ID of the incident file to be modified
     *
     * @return Response A rendered form view for GET requests or after unsuccessful POST submissions,
     *                  or a redirect response after successful modification
     */
    #[Route('/incident/modify_incident/{incidentId}', name: 'incident_modify_file')]
    public function modify_incident_file(Request $request, int $incidentId): Response
    {
        // Retrieve the current incident entity based on the incidentId
        $incident = $this->entityManagerFacade->find('incident', $incidentId);

        if ($request->isMethod('POST')) {
            // Check name compliency and adjust if necessary
            $this->contentManagerFacade->requestIncidentFilenameChecks($request);

            // Create a form to modify the Upload entity
            $form = $this->createForm(IncidentType::class, $incident);

            // Handle the form data on POST requests
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Process the form data and modify the Upload entity
                try {
                    $name = $this->incidentService->modifyIncidentFile($incident);
                    $this->logger->notice('IncidentController::modifyIncidentFile - File ' . $name . 'modified successfully by user ' . $this->getUser()->getUsername());
                    $this->addFlash('success', 'File ' . $name . 'modified successfully');
                } catch (\Exception $e) {
                    $this->logger->error('Error modifying file', [$e->getMessage()]);
                    $this->addFlash('danger', 'Error modifying file. Error: ' . $e->getMessage());
                }
            } elseif ($form->isSubmitted()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            } else {
                $this->addFlash('error', 'Invalid form. Could not get submitted. Check the entered data.');
            }
        }

        // Create a form to modify the Upload entity
        $form = $this->createForm(IncidentType::class, $incident);
        $productLine = $incident->getProductLine();
        // If it's a GET request, render the form
        return $this->render('services/incident/incident_modification.html.twig', [
            'form'          => $form->createView(),
            'zone'          => $productLine->getZone(),
            'productLine'   => $productLine,
            'incident'      => $incident
        ]);
    }

    /**
     * Redirects the user back to the page they came from.
     *
     * This utility method extracts the referrer URL from the request headers
     * and creates a redirect response to that URL, effectively sending the user
     * back to the previous page after an action is completed.
     *
     * @param Request $request The HTTP request object containing the referrer information
     *
     * @return Response A redirect response to the originating URL
     */
    public function redirectToOriginUrl($request): Response
    {
        $originUrl = $request->headers->get('referer');
        return $this->redirect($originUrl);
    }
}
