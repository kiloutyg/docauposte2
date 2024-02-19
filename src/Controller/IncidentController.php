<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


use App\Entity\IncidentCategory;

use App\Form\IncidentType;


// This controller manage the logics of the incidents interface, it is responsible for rendering the incidents interface.
// It is also responsible for creating, modifying, deleting incidents and incident categories.
#[Route('/', name: 'app_')]
class IncidentController extends FrontController
{
    // Render the incidents page and filter the incidents by productline and sort them by id ascending to display them in the right order
    #[Route('/zone/{zoneId}/productline/{productlineId}/incident/{incidentId}', name: 'mandatory_incident')]
    public function mandatoryIncident(int $productlineId = null, int $incidentId = null): Response
    {
        $incidentEntity = null;
        if ($incidentId != null) {
            $incidentEntity = $this->incidentRepository->find($incidentId);
        }

        // If the incident does not exist, we get the productline entity from the productline name
        if (!$incidentEntity) {
            $productLine = $this->productLineRepository->find($productlineId);
        } else {
            $productLine = $incidentEntity->getProductLine();
        }

        $zone        = $productLine->getZone();
        $incidents   = [];

        // Get all the incidents of the productline and sort them by id ascending
        $incidents = $this->incidentRepository->findBy(
            ['ProductLine' => $productlineId],
            ['id' => 'ASC'] // order by id ascending
        );

        // Get the id of each incident and put them in an array
        $incidentIds = array_map(function ($incident) {
            return $incident->getId();
        }, $incidents);

        // Get the key of the current incident in the array
        $currentIncidentKey = array_search($incidentId, $incidentIds);

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
                    'incidentId'        => $incident ? $incident->getId() : null,
                    'incident'          => $incident,
                    'incidentCategory'  => $incident ? $incident->getIncidentCategory() : null,
                    'incidents'         => $incidents,
                    'productlineId'     => $productLine->getId(),
                    'zoneId'            => $zone->getId(),
                    'nextIncidentId'    => $nextIncident ? $nextIncident->getId() : null
                ]
            );
            // If there is no incident we render the productline page
        } else {
            return $this->render(
                'productline.html.twig',
                [
                    'zone'        => $zone,
                    'productLine' => $productLine
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
    #[Route('/incident/delete/incident_incidentsCategory_deletion/{incidentCategoryId}', name: 'incident_incidentsCategory_deletion')]
    public function incidentCategoryDeletion(int $incidentCategoryId, Request $request): Response
    {
        $entityType = "incidentCategory";
        $entity = $this->entitydeletionService->deleteEntity($entityType, $incidentCategoryId);
        $originUrl = $request->headers->get('referer');

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirect($originUrl);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirect($originUrl);
        }
    }



    // Create a route to upload an incident file. It depends on the IncidentService.
    #[Route('/incident/incident_uploading', name: 'generic_upload_incident_files')]
    public function generic_upload_incident_files(Request $request): Response
    {

        $originUrl = $request->headers->get('referer');

        // Check if the form is submitted 
        if ($request->isMethod('POST')) {

            $productline = $request->request->get('incident_productline');
            $newname = $request->request->get('incidents_newFileName');
            $IncidentCategoryId = $request->request->get('incidents_incidentsCategory');
            $IncidentCategory = $this->incidentCategoryRepository->findoneBy(['id' => $IncidentCategoryId]);
            $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productline]);
            $user = $this->getUser();

            // Use the IncidentService to handle the upload of the Incidents files
            $name = $this->incidentService->uploadIncidentFiles($request, $productlineEntity, $IncidentCategory, $user, $newname);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');
            return $this->redirect($originUrl);
        } else {
            // Show an error message if the form is not submitted
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirect($originUrl);
        }
    }


    // Create a route to download a file, in more simple terms, to display a file.
    #[Route('/download_incident/{incidentId}', name: 'incident_download_file')]
    public function download_file(int $incidentId = null): Response
    {
        $file       = $this->incidentRepository->find($incidentId);
        $path       = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // Create a route to visualize the file in the modifcation view.
    #[Route('/incident/modify_download_incident/{id}', name: 'modify_incident_download_file')]
    public function modify_download_file(int $id = null): Response
    {
        $file       = $this->incidentRepository->findOneBy(['id' => $id]);
        $path       = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    // Create a route to delete a file
    #[Route('/incident/delete/{productlineId}/{incidentId}', name: 'incident_delete_file')]
    public function delete_file(int $incidentId, int $productlineId, Request $request): Response
    {
        $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productlineId]);
        $originUrl = $request->headers->get('referer');
        $incidentEntity = $this->incidentRepository->find($incidentId);

        // Check if the user is the creator of the upload or if he is a super admin
        if ($this->authChecker->isGranted('ROLE_ADMIN')) {
            // Use the incidentService to handle file deletion
            $name = $this->incidentService->deleteIncidentFile($incidentEntity, $productlineEntity);
        } else if ($this->getUser() === $incidentEntity->getUploader()) {
            // Use the incidentService to handle file deletion
            $name = $this->incidentService->deleteIncidentFile($incidentEntity, $productlineEntity);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce document.');
            return $this->redirectToRoute($originUrl);
        }
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirect($originUrl);
    }


    // Create a route to modify a file and or display the modification page
    #[Route('/incident/modify_incident/{incidentId}', name: 'incident_modify_file')]
    public function modify_incident_file(Request $request, int $incidentId): Response
    {
        // Retrieve the current incident entity based on the incidentId
        $incident = $this->incidentRepository->find($incidentId);
        $productLine = $incident->getProductLine();
        $zone = $productLine->getZone();
        $originUrl = $request->headers->get('referer');
        $user = $this->getUser();

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
                $this->incidentService->modifyIncidentFile($incident, $user);
                $this->addFlash('success', 'Le fichier a été modifié.');
                return $this->redirect($originUrl);
            } catch (\Exception $e) {
                // $this->addFlash('error', $e->getMessage());
                $this->addFlash('error', 'Le fichier n\'a pas été modifié. Veuillez réessayer.');

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
