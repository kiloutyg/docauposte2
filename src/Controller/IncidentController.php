<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


use App\Service\IncidentsService;

use App\Entity\IncidentCategory;

use App\Form\IncidentType;


// This controller manage the logics of the incidents interface, it is responsible for rendering the incidents interface.
// It is also responsible for creating, modifying, deleting incidents and incident categories.
#[Route('/', name: 'app_')]
class IncidentController extends FrontController
{
    // Render the incidents page and filter the incidents by productline and sort them by id ascending to display them in the right order
    #[Route('/zone/{zone}/productline/{productline}/incident/{incidentid}', name: 'mandatory_incident')]
    public function mandatoryIncident(string $productline = null, int $incidentid = null): Response
    {
        // Get the incident entity based on the incident id in the url
        $incidentEntity = $this->incidentRepository->findOneBy(['id' => $incidentid]);

        // If the incident does not exist, we get the productline entity from the productline name
        if (!$incidentEntity) {
            $productLine = $this->productLineRepository->findOneBy(['name' => $productline]);
        } else {
            $productLine = $incidentEntity->getProductLine();
        }

        $zone        = $productLine->getZone();
        $incidents   = [];

        // Get all the incidents of the productline and sort them by id ascending
        $incidents = $this->incidentRepository->findBy(
            ['ProductLine' => $productLine->getId()],
            ['id' => 'ASC'] // order by id ascending
        );

        // Get the id of each incident and put them in an array
        $incidentIds = array_map(function ($incident) {
            return $incident->getId();
        }, $incidents);

        // Get the key of the current incident in the array
        $currentIncidentKey = array_search($incidentid, $incidentIds);

        // Get the current incident
        $incident = $incidents[$currentIncidentKey];

        // If the current incident does not exist, we set it to null
        if ($currentIncidentKey === false) {
            $incident = null;
        }

        // Get the next incident key in the array 
        $nextIncidentKey = $currentIncidentKey + 1;

        // Get the next incident in the array based on the next incident key
        $nextIncident  = isset($incidents[$nextIncidentKey]) ? $incidents[$nextIncidentKey] : null;

        // If there is an incident we render the incidents page with the incident data and the next incident id to redirect to the next incident page
        if ($incident) {
            return $this->render(
                '/services/incidents/incidents_view.html.twig',
                [
                    'incidentid'        => $incident ? $incident->getId() : null,
                    'incident'          => $incident,
                    'incidentCategory'  => $incident ? $incident->getIncidentCategory() : null,
                    'incidents'         => $incidents,
                    'productline'       => $productLine->getName(),
                    'zone'              => $zone->getName(),
                    'nextIncidentId'    => $nextIncident ? $nextIncident->getId() : null
                ]
            );
            // If there is no incident we render the productline page
        } else {
            return $this->render(
                'productline.html.twig',
                [
                    'zone'        => $zone,
                    'categories'  => $this->categoryRepository->findAll(),
                    'productLine' => $productLine,
                ]
            );
        }
    }


    // Logic to create a new IncidentCategory and display a message
    #[Route('/incident/incident_incidentsCategory_creation', name: 'incident_incidentsCategory_creation')]
    public function incidentCategoryCreation(Request $request): JsonResponse
    {
        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        // Get the name of the incident category
        $incidentCategoryName = $data['incident_incidentsCategory_name'] ?? null;

        // Get the existing incident category name
        $existingIncidentCategory = $this->incidentCategoryRepository->findOneBy(['name' => $incidentCategoryName]);

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
            $this->em->persist($incidentCategory);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Le type d\'incident a été créé']);
        }
    }

    // Create a route for incidentCategory deletion. It depends on the entitydeletionService.
    #[Route('/incident/delete/incident_incidentsCategory_deletion/{incidentCategory}', name: 'incident_incidentsCategory_deletion')]
    public function incidentCategoryDeletion(string $incidentCategory, Request $request): Response
    {
        $entityType = "incidentCategory";
        $entityid = $this->incidentCategoryRepository->findOneBy(['name' => $incidentCategory]);
        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());
        $originUrl = $request->headers->get('referer');

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirect($originUrl);
        }
    }



    // Create a route to upload an incident file. It depends on the IncidentsService.
    #[Route('/incident/incident_uploading', name: 'generic_upload_incident_files')]
    public function generic_upload_incident_files(IncidentsService $incidentsService, Request $request): Response
    {
        $this->incidentsService = $incidentsService;

        $originUrl = $request->headers->get('referer');

        // Check if the form is submitted 
        if ($request->isMethod('POST')) {

            $productline = $request->request->get('incident_productline');
            $newname = $request->request->get('incidents_newFileName');
            $IncidentCategoryId = $request->request->get('incidents_incidentsCategory');
            $IncidentCategory = $this->incidentCategoryRepository->findoneBy(['id' => $IncidentCategoryId]);
            $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productline]);

            // Use the IncidentsService to handle the upload of the Incidents files
            $name = $this->incidentsService->uploadIncidentFiles($request, $productlineEntity, $IncidentCategory, $newname);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');
            return $this->redirect($originUrl);
        } else {
            // Show an error message if the form is not submitted
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirect($originUrl);
        }
    }


    // Create a route to download a file, in more simple terms, to display a file.
    #[Route('/download_incident/{name}', name: 'incident_download_file')]
    public function download_file(string $name = null): Response
    {
        $file = $this->incidentRepository->findOneBy(['name' => $name]);
        $path = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // Create a route to delete a file
    #[Route('/incident/delete/{productline}/{name}', name: 'incident_delete_file')]
    public function delete_file(string $name = null, string $productline = null, IncidentsService $incidentsService, Request $request): Response
    {
        $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productline]);
        $originUrl = $request->headers->get('referer');

        // Use the incidentsService to handle file deletion
        $name = $incidentsService->deleteIncidentFile($name, $productlineEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirect($originUrl);
    }


    // Create a route to modify a file and or display the modification page
    #[Route('/modify_incident/{incidentId}', name: 'incident_modify_file')]
    public function modify_incident_file(Request $request, int $incidentId, incidentsService $incidentsService): Response
    {
        // Retrieve the current incident entity based on the incidentId
        $incident = $this->incidentRepository->findOneBy(['id' => $incidentId]);
        $productLine = $incident->getProductLine();
        $zone = $productLine->getZone();
        $originUrl = $request->headers->get('referer');

        if (!$incident) {
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirect($originUrl);
        }

        // Create a form to modify the Upload entity
        $form = $this->createForm(IncidentType::class, $incident);

        // Handle the form data on POST requests
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                $incidentsService->modifyIncidentFile($incident);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirect($originUrl);
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirect($originUrl);
            }
        }

        // Convert the errors to an array
        $errorMessages = [];
        if ($form->isSubmitted() && !$form->isValid()) {
            // Get form errors
            $errors = $form->getErrors(true);

            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirect($originUrl);
        }

        // If it's a POST request but the form is not valid or not submitted
        if ($request->isMethod('POST')) {
            $this->addFlash('error', 'Invalid form. Errors: ' . implode(', ', $errorMessages));
            return $this->redirect($originUrl);
        }

        // If it's a GET request, render the form
        return $this->render('services/incidents/incidents_modification.html.twig', [
            'form'          => $form->createView(),
            'zone'          => $zone,
            'productLine'   => $productLine,
            'incident'      => $incident
        ]);
    }
}