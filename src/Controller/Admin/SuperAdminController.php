<?php

namespace App\Controller\Admin;

use App\Entity\Zone;

use App\Service\EntityFetchingService;
use App\Service\IncidentService;
use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\FolderService;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


// This controller is responsible for rendering the super admin interface an managing the logic of the super admin interface
class SuperAdminController extends AbstractController
{
    private $em;
    private $incidentRepository;
    private $entityFetchingService;
    private $folderService;
    private $entitydeletionService;
    private $incidentService;
    private $uploadService;

    public function __construct(

        EntityManagerInterface          $em,

        EntityFetchingService           $entityFetchingService,
        UploadService                   $uploadService,
        EntityDeletionService           $entitydeletionService,
        FolderService                   $folderService,
        IncidentService                 $incidentService,

    ) {
        $this->em                           = $em;

        $this->uploadService                = $uploadService;
        $this->folderService                = $folderService;
        $this->incidentService              = $incidentService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->entityFetchingService        = $entityFetchingService;
    }



    // This function is responsible for rendering the super admin interface
    #[Route('/super_admin', name: 'app_super_admin')]
    public function superAdmin(): Response
    {
        $pageLevel = 'super';

        $incidents = $this->entityFetchingService->getIncidents();

        $uploads = $this->entityFetchingService->getAllWithAssociations();

        $uploadsArray = $this->uploadService->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->incidentService->groupIncidents($incidents);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zones'                     => $this->entityFetchingService->getZones(),
        ]);
    }






    // Zone creation logic destined to the super admin, it also creates the folder structure for the zone
    #[Route('/super_admin/create_zone', name: 'app_super_admin_create_zone')]
    public function createZone(Request $request)
    {
        // Create a zone
        if ($request->getMethod() == 'POST') {
            $zonename = trim($request->request->get('zonename'));
            $zone = $this->entityFetchingService->findOneBy('zone', ['name' => $zonename]);
            if (empty($zonename)) {
                $this->addFlash('danger', 'Le nom de la Zone ne peut être vide');
                return $this->redirectToRoute('app_super_admin');
            }

            if ($zone) {
                $this->addFlash('danger', 'La zone existe déjà');
                return $this->redirectToRoute('app_super_admin');
            } else {
                $count = $this->entityFetchingService->count('zone', []);
                $sortOrder = $count + 1;
                $zone = new Zone();
                $zone->setName($zonename);
                $zone->setSortOrder($sortOrder);
                $zone->setCreator($this->getUser());
                $this->em->persist($zone);
                $this->em->flush();
                $this->folderService->folderStructure($zonename);
                $this->addFlash('success', 'La zone a été créée');
                return $this->redirectToRoute('app_super_admin');
            }
        }
    }

    // Zone deletion logic destined to the super admin, it also deletes the folder structure for the zone
    #[Route('/super_admin/delete_zone/{zoneId}', name: 'app_super_admin_delete_zone')]
    public function deleteEntityZone(int $zoneId): Response
    {
        $entityType = 'zone';

        $response = $this->entitydeletionService->deleteEntity($entityType, $zoneId);

        if ($response) {
            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }







    // Update method for any stuff necessary during dev
    #[Route('/super_admin/update', name: 'super_admin_update')]
    public function updateDB()
    {
        $incidents = $this->entityFetchingService->getIncidents();
        foreach ($incidents as $incident) {
            $similarNamedincidents = $this->incidentRepository->findBy(['name' => $incident->getName()]);
            foreach ($similarNamedincidents as $similarNamedincident) {
                if ($incident->getId() != $similarNamedincident->getId()) {
                    $originalName = pathinfo($similarNamedincident->getName(), PATHINFO_FILENAME);
                    $fileExtension = pathinfo($similarNamedincident->getName(), PATHINFO_EXTENSION);
                    $similarNamedincident->setName($originalName . '_' . uniqid('', true) . '.' . $fileExtension);
                    $this->em->persist($similarNamedincident);
                }
            }
        }

        $this->em->flush();
        return $this->redirectToRoute('app_base');
    }
}
