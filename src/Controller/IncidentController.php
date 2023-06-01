<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentTypeRepository;

use App\Service\IncidentsService;

use App\Entity\Incident;
use App\Entity\IncidentType;

#[Route('/', name: 'app_')]

class IncidentController extends FrontController
{
    // Incident mandatory page
    #[Route('/zone/{zone}/productline/{productline}/incident', name: 'mandatory_incident')]
    public function mandatoryIncident(string $productline = null): Response
    {
        $productLine = $this->productLineRepository->findoneBy(['name' => $productline]);
        $zone        = $productLine->getZone();
        $incidents = [];
        $incidents = $this->incidentRepository->findBy(['ProductLine' => $productLine->getId()]);


        return $this->render(
            '/services/incidents/incidents_view.html.twig',
            [
                'incidents'    => $incidents,
                'productLine'  => $productLine,

            ]
        );
    }


    #[Route('/incident_type_creation', name: 'incident_type_creation')]
    public function incidentTypeCreation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $incidentTypename = $data['incident_type_name'] ?? null;

        $existingIncidentType = $this->incidentTypeRepository->findOneBy(['name' => $incidentTypename]);

        if (empty($incidentTypename)) {
            return new JsonResponse(['success' => false, 'message' => 'Le nom du type d\'incident ne peut pas être vide']);
        }
        if ($existingIncidentType) {
            return new JsonResponse(['success' => false, 'message' => 'Ce type d\'incident existe déjà']);
        } else {
            $incidentType = new IncidentType();
            $incidentType->setName($incidentTypename);
            $this->em->persist($incidentType);
            $this->em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Le type d\'incident a été créé']);
        }
    }



    // create a route to upload a file
    #[Route('/incident_uploading', name: 'generic_upload_incident_files')]
    public function generic_upload_incident_files(IncidentsService $incidentsService, Request $request): Response
    {
        $this->incidentsService = $incidentsService;
        // Check if the form is submitted
        if ($request->isMethod('POST')) {

            $productline = $request->request->get('incidents_productline');
            $newname = $request->request->get('incidents_newFileName');
            $type = $request->request->get('incidents_type');
            $IncidentType = $this->incidentTypeRepository->findoneBy(['id' => $type]);

            $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productline]);

            // Use the IncidentsService to handle file Incidents
            $name = $this->incidentsService->uploadIncidentFiles($request, $productlineEntity, $newname, $IncidentType);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');

            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'         => $this->zoneRepository->findAll(),
                    'productlines'  => $this->productLineRepository->findAll(),
                    'categories'    => $this->categoryRepository->findAll(),
                    'incidents'     => $this->incidentRepository->findAll(),
                ]
            );
        } else {
            // Show an error message if the form is not submitted
            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'         => $this->zoneRepository->findAll(),
                    'productlines'  => $this->productLineRepository->findAll(),
                    'categories'    => $this->categoryRepository->findAll(),
                    'incidents'     => $this->incidentRepository->findAll(),
                ]
            );
            // Redirect the user to an appropriate page or show an error message
        }
    }


    // create a route to download a file
    #[Route('/download_incident/{name}', name: 'incident_download_file')]
    public function download_file(string $name = null): Response
    {
        $file = $this->incidentRepository->findOneBy(['name' => $name]);
        $path = $file->getPath();
        $file       = new File($path);
        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    // create a route to delete a file
    #[Route('/delete/{productline}/{name}', name: 'incident_delete_file')]

    public function delete_file(string $name = null, string $productline = null, IncidentsService $incidentsService): Response
    {
        $productlineEntity = $this->productLineRepository->findoneBy(['id' => $productline]);

        // Use the incidentsService to handle file deletion
        $name = $incidentsService->deleteIncidentFile($name, $productlineEntity);
        $this->addFlash('success', 'File ' . $name . ' deleted');

        return $this->redirectToRoute(
            'app_base',
            [
                'zones'       => $this->zoneRepository->findAll(),
                'productlines' => $this->productLineRepository->findAll(),
                'incidents'     => $this->incidentRepository->findAll(),
            ]
        );
    }


    // create a route to display the modification page 
    #[Route('/modifyfile/{incidentId}', name: 'incident_modify_file_page')]

    public function modify_file_page(string $incidentId = null): Response
    {
        $incident = $this->incidentRepository->findoneBy(['id' => $incidentId]);
        $productLine = $incident->getProductLine();
        $zone = $productLine->getZone();


        $form = $this->createForm(IncidentType::class, $incident);
        return $this->render(
            'services/uploads/uploads_modification.html.twig',
            [
                'upload'      => $incident,
                'zone'        => $zone,
                'productLine' => $productLine,

                'form' => $form->createView(),
            ]
        );
    }



    // create a route to modify a file and or display the modification page
    #[Route('/modify/{incidentId}', name: 'incident_modify_file')]
    public function modify_file(Request $request, int $incidentId, incidentsService $incidentsService, LoggerInterface $logger): Response
    {
        // Retrieve the current upload entity based on the incidentId
        $incident = $this->incidentRepository->findOneBy(['id' => $incidentId]);
        $productLine = $incident->getproductline();
        $zone = $productLine->getZone();

        if (!$incident) {
            $logger->error('Upload not found', ['incidentId' => $incidentId]);
            $this->addFlash('error', 'Le fichier n\'a pas été trouvé.');
            return $this->redirectToRoute('app_base');
        }

        $logger->info('Retrieved Incident entity:', ['incident' => $incident]);

        // Get form data
        $formData = $request->request->all();
        $logger->info('Form data before any manipulation:', ['formData' => $formData]);

        // Create a form to modify the Upload entity
        $form = $this->createForm(UploadType::class, $incident);

        $logger->info('Form data before manipulation:', ['formData' => $formData]);

        // Handle the form data on POST requests
        $logger->info('Form data before handleRequest:', ['formData' => $formData]);

        $form->handleRequest($request);

        $logger->info('Form data after handleRequest:', ['formData' => $form->getData()]);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form data and modify the Upload entity
            try {
                $incidentsService->modifyIncidentFile($incident);

                $this->addFlash('success', 'Le fichier a été modifié.');
                $logger->info('File modified successfully', ['upload' => $incident]);
                return $this->redirectToRoute('app_base');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur s\'est produite lors de la modification du fichier.');
                $logger->error('Failed to modify file', ['upload' => $incident, 'error' => $e->getMessage()]);

                $response = [
                    'status' => 'error',
                    'message' => 'Une erreur s\'est produite lors de la modification du fichier.',
                    'error' => $e->getMessage(),
                ];

                return new JsonResponse($response);
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

            // Print form errors
            $logger->error('Form validation errors:', ['errors' => $errorMessages]);

            // Return the errors in the JSON response
            $this->addFlash('error', 'Invalid form. Check the entered data.');
            return $this->redirectToRoute('app_base', ['incidentId' => $incidentId]);
        }

        // If it's a POST request but the form is not valid or not submitted
        if ($request->isMethod('POST')) {
            $this->addFlash('error', 'Invalid form. Errors: ' . implode(', ', $errorMessages));
            $logger->info('Submitted data:', $request->request->all());

            return $this->redirectToRoute('app_base', ['incidentId' => $incidentId]); // Return a 400 Bad Request response
        }

        // If it's a GET request, render the form
        return $this->render('services/uploads/uploads_modification.html.twig', [
            'form' => $form->createView(),
            'zone'        => $zone,
            'productLine' => $productLine,
            'category'    => $category,
            'productline'      => $productline,
            'upload' => $incident
        ]);
    }
}