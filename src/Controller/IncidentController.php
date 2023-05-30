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

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\IncidentRepository;

use App\Service\IncidentsService;

class IncidentController extends FrontController
{
    #[Route('/incident', name: 'app_incident')]
    public function index(): Response
    {
        return $this->render('/services/incidents/incidents.html.twig', []);
    }


    // create a route to upload a file
    #[Route('/uploading', name: 'generic_upload_files')]
    public function generic_upload_files(IncidentsService $incidentsService, Request $request): Response
    {
        $this->incidentsService = $incidentsService;
        // Check if the form is submitted
        if ($request->isMethod('POST')) {

            $productline = $request->request->get('productline');
            $newFileName = $request->request->get('newFileName');


            $buttonEntity = $this->productlineRepository->findoneBy(['id' => $productline]);

            // Use the IncidentsService to handle file Incidents
            $name = $this->incidentsService->uploadFiles($request, $buttonEntity, $newFileName);
            $this->addFlash('success', 'Le document '  . $name .  ' a été correctement chargé');

            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'       => $this->zoneRepository->findAll(),
                    'productlines' => $this->productLineRepository->findAll(),
                    'categories'  => $this->categoryRepository->findAll(),
                    'buttons'     => $this->buttonRepository->findAll(),
                    'Incidents'     => $this->uploadRepository->findAll(),
                ]
            );
        } else {
            // Show an error message if the form is not submitted

            $this->addFlash('error', 'Le fichier n\'a pas été poster correctement.');
            return $this->redirectToRoute(
                'app_base',
                [
                    'zones'       => $this->zoneRepository->findAll(),
                    'productlines' => $this->productLineRepository->findAll(),
                    'categories'  => $this->categoryRepository->findAll(),
                    'buttons'     => $this->buttonRepository->findAll(),
                    'Incidents'     => $this->uploadRepository->findAll(),
                ]
            );
            // Redirect the user to an appropriate page or show an error message
        }
    }
}