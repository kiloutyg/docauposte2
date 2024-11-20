<?php


namespace App\Controller;

use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\ProductLine;
use App\Entity\Zone;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;

use App\Service\EntityDeletionService;
use App\Service\UploadService;
use App\Service\FolderCreationService;
use App\Service\IncidentService;
use App\Service\EntityHeritanceService;
use App\Service\SettingsService;
use App\Service\EntityFetchingService;
use App\Service\ErrorService;

// This controller is responsible for rendering the zone admin interface an managing the logic of the zone admin interface
class ZoneAdminController extends AbstractController
{

    private $em;
    private $logger;
    private $authChecker;

    // Repository methods
    private $zoneRepository;
    private $productLineRepository;


    // Services methods
    private $incidentService;
    private $folderCreationService;
    private $entityHeritanceService;
    private $entitydeletionService;
    private $uploadService;
    private $settingsService;
    private $entityFetchingService;
    private $errorService;


    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,

        // Repository methods
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,


        // Services methods
        IncidentService                 $incidentService,
        EntityHeritanceService          $entityHeritanceService,
        FolderCreationService           $folderCreationService,
        EntityDeletionService           $entitydeletionService,
        UploadService                   $uploadService,
        SettingsService                 $settingsService,
        EntityFetchingService           $entityFetchingService,
        ErrorService                    $errorService,

    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;

        // Variables related to the repositories
        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;

        // Variables related to the services
        $this->incidentService              = $incidentService;
        $this->entityHeritanceService       = $entityHeritanceService;
        $this->folderCreationService        = $folderCreationService;
        $this->uploadService                = $uploadService;
        $this->entitydeletionService        = $entitydeletionService;
        $this->settingsService              = $settingsService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->errorService                 = $errorService;
    }


    // This function is responsible for rendering the zone admin interface
    #[Route('/zone_admin/{zoneId}', name: 'app_zone_admin')]
    public function zoneAdmin(int $zoneId = null, Zone $zone = null): Response
    {
        $pageLevel = 'zone';
        if ($zone === null) {
            $zone = $this->zoneRepository->find($zoneId);
        }
        if (!$zone) {
            return $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $productLines = $zone->getProductLines();

        $uploads = $this->entityHeritanceService->uploadsByParentEntity('zone', $zone);
        $incidents = $this->entityHeritanceService->incidentsByParentEntity('zone', $zone);

        // Group the uploads and incidents by parent entity
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
            'zone'                      => $zone,
            'zoneProductLines'          => $productLines,
        ]);
    }



    // Creation of new productline
    #[Route('/zone_admin/create_productline/{zoneId}', name: 'app_zone_admin_create_productline')]
    public function createProductLine(Request $request, int $zoneId = null)
    {
        $zone = $this->zoneRepository->find(['id' => $zoneId]);

        if (!preg_match("/^[^.]+$/", $request->request->get('productlinename'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->zoneAdmin(null, $zone);
        } else {
            // Check if the productline already exists by comparing the productline name and the zone
            $productlinename = $request->request->get('productlinename') . '.' . $zone->getName();
            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);

            if ($productline) {
                $this->addFlash('danger', 'La ligne de produit existe déjà');
                return $this->zoneAdmin(null, $zone);
                // Create a productline

            } else {
                $count = $this->productLineRepository->count(['zone' => $zone->getId()]);
                $sortOrder = $count + 1;
                $productline = new ProductLine();
                $productline->setName($productlinename);
                $productline->setZone($zone);
                $productline->setSortOrder($sortOrder);
                $productline->setCreator($this->getUser());
                $this->em->persist($productline);
                $this->em->flush();
                $this->folderCreationService->folderStructure($productlinename);
                $this->addFlash('success', 'The Product Line has been created');
                return $this->zoneAdmin(null, $zone);
            }
        }
    }


    // Delete a productline and all its children entities, it depends on the entitydeletionService
    #[Route('/zone_admin/delete_productline/{productLineId}', name: 'app_zone_admin_delete_productline')]
    public function deleteEntityProductLine(int $productLineId): Response
    {
        $entityType = 'productline';
        $productLine = $this->productLineRepository->find($productLineId);
        if (empty($productLine)) {
            return $this->redirectToRoute('app_base');
        };

        $zone = $productLine->getZone();

        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN") || $this->getUser() === $productLine->getCreator()) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $productLine->getId());
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ligne.');
            return $this->zoneAdmin(null, $zone);
        }

        if ($response == true) {
            $this->addFlash('success', 'La ligne de produit ' . $entityType . ' a été supprimée');
            return $this->zoneAdmin(null, $zone);
        } else {
            $this->addFlash('danger', 'La ligne de produit ' . $entityType . ' n\'existe pas');
            return $this->zoneAdmin(null, $zone);
        }
    }
}
