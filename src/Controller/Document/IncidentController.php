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

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\IncidentCategory;

use App\Form\IncidentType;

use App\Service\IncidentService;
use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;

// This controller manage the logics of the incidents interface, it is responsible for rendering the incidents interface.
// It is also responsible for creating, modifying, deleting incidents and incident categories.
#[Route('/', name: 'app_')]
class IncidentController extends AbstractController
{
    private $logger;
    private $authChecker;

    // Repository methods

    // Services methods
    private $incidentService;
    private $entityManagerFacade;
    private $contentManagerFacade;

    public function __construct(
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        // Services methods
        IncidentService                 $incidentService,
        EntityManagerFacade             $entityManagerFacade,
        ContentManagerFacade            $contentManagerFacade,

    ) {
        $this->authChecker                  = $authChecker;
        $this->logger                       = $logger;

        // Variables related to the services
        $this->incidentService              = $incidentService;
        $this->contentManagerFacade         = $contentManagerFacade;
        $this->entityManagerFacade          = $entityManagerFacade;
    }




    // Render the Incident management view in any role level admin page
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
    #[Route('/incident/incident_uploading', name: 'generic_upload_incident_files')]
    public function genericUploadOfIncidentFiles(Request $request): Response
    {

        // Check if the form is submitted
        if ($request->isMethod('POST')) {
            // Use the IncidentService to handle the upload of the Incidents files
            $name = $this->incidentService->uploadIncidentFiles($request);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');
            return $this->redirectToOriginUrl($request);
        } else {
            // Show an error message if the form is not submitted
            $this->logger->info('Le fichier n\'a pas été poster correctement.');
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirectToOriginUrl($request);
        }
    }




    // Create a route to download a file, in more simple terms, to display a file.
    #[Route('/download_incident/{incidentId}', name: 'incident_download_file')]
    public function download_file(?int $incidentId = null): Response
    {
        $file       = $this->entityManagerFacade->find('incident', $incidentId);
        $path       = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }





    // Create a route to delete a file
    #[Route('/delete/incident/{incidentId}', name: 'incident_delete_file')]
    public function delete_file(int $incidentId, Request $request): Response
    {
        $incidentEntity = $this->entityManagerFacade->find('incident', $incidentId);
        $this->logger->debug('Deleting file: ' . $incidentEntity->getPath());
        $name = $this->incidentService->deleteIncidentFile($incidentEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');
        return $this->redirectToOriginUrl($request);
    }




    // Create a route to modify a file and or display the modification page
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
                return $this->incidentService->modifyIncidentFile($incident);
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

    public function redirectToOriginUrl($request): Response
    {
        $originUrl = $request->headers->get('referer');
        return $this->redirect($originUrl);
    }
}
