<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Zone;

use App\Repository\ZoneRepository;

use App\Service\EntityFetchingService;
use App\Service\TrainingRecordService;
use App\Service\IncidentService;
use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\FolderCreationService;

// This controller is responsible for rendering the super admin interface an managing the logic of the super admin interface
class SuperAdminController extends AbstractController
{
    private $projectDir;
    private $public_dir;
    private $em;

    private $zoneRepository;

    private $entityFetchingService;
    private $trainingRecordService;
    private $folderCreationService;
    private $entitydeletionService;
    private $incidentService;
    private $uploadService;

    public function __construct(

        EntityManagerInterface          $em,
        ParameterBagInterface           $params,

        ZoneRepository                  $zoneRepository,

        EntityFetchingService           $entityFetchingService,
        TrainingRecordService           $trainingRecordService,
        UploadService                   $uploadService,
        EntityDeletionService           $entitydeletionService,
        FolderCreationService           $folderCreationService,
        IncidentService                 $incidentService,

    ) {
        $this->em                           = $em;
        $this->projectDir                   = $params->get('kernel.project_dir');
        $this->public_dir                   = $this->projectDir . '/public';

        $this->zoneRepository               = $zoneRepository;

        $this->uploadService                = $uploadService;
        $this->folderCreationService        = $folderCreationService;
        $this->incidentService              = $incidentService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->trainingRecordService        = $trainingRecordService;
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
            $zone = $this->zoneRepository->findOneBy(['name' => $zonename]);
            if (empty($zonename)) {
                $this->addFlash('danger', 'Le nom de la Zone ne peut être vide');
                return $this->redirectToRoute('app_super_admin');
            }

            if (!file_exists($this->public_dir . '/doc/')) {
                mkdir($this->public_dir . '/doc/', 0777, true);
            }

            if ($zone) {
                $this->addFlash('danger', 'La zone existe déjà');
                return $this->redirectToRoute('app_super_admin');
            } else {
                $count = $this->zoneRepository->count([]);
                $sortOrder = $count + 1;
                $zone = new Zone();
                $zone->setName($zonename);
                $zone->setSortOrder($sortOrder);
                $zone->setCreator($this->getUser());
                $this->em->persist($zone);
                $this->em->flush();
                $this->folderCreationService->folderStructure($zonename);
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

        $entity = $this->zoneRepository->findOneBy(['id' => $zoneId]);

        if (empty($entity)) {
            return $this->redirectToRoute('app_super_admin');
        };

        $response = $this->entitydeletionService->deleteEntity($entityType, $entity->getId());

        if ($response == true) {
            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }


    // A route that use a method to revalid automatically every training records of a certain date
    #[Route('super_admin/cheattrain/{year}/{month}/{day}', name: 'app_super_admin_cheat_train')]
    public function cheatTrain(string $year, string $month, string $day)
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $date = $year . '-' . $month . '-' . $day;
            $this->trainingRecordService->cheatTrain($date);
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
    }
}
