<?php


namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\ProductLine;
use App\Entity\Zone;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;

#[Route('/zone_admin', name: 'app_zone_')]
// This controller is responsible for rendering the zone admin interface an managing the logic of the zone admin interface
class ZoneAdminController extends AbstractController
{
    private $entityManagerFacade;
    private $contentManagerFacade;
    private $authChecker;
    private $errorService;

    public function __construct(
        EntityManagerFacade $entityManagerFacade,
        ContentManagerFacade $contentManagerFacade,
        AuthorizationCheckerInterface $authChecker,
        ErrorService $errorService
    ) {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->contentManagerFacade = $contentManagerFacade;
        $this->authChecker = $authChecker;
        $this->errorService = $errorService;
    }

    // This function is responsible for rendering the zone admin interface
    #[Route('/{zoneId}', name: 'admin')]
    public function zoneAdmin(?int $zoneId = null, ?Zone $zone = null): Response
    {
        $pageLevel = 'zone';
        if ($zone === null && $zoneId != null) {
            $zone = $this->entityManagerFacade->find('zone', $zoneId);
        }
        if (!$zone) {
            return $this->errorService->errorRedirectByOrgaEntityType($pageLevel);
        }

        $productLines = $zone->getProductLines();

        $uploads = $this->entityManagerFacade->uploadsByParentEntity('zone', $zone);
        $incidents = $this->entityManagerFacade->incidentsByParentEntity('zone', $zone);

        // Group the uploads and incidents by parent entity
        $uploadsArray = $this->contentManagerFacade->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->contentManagerFacade->groupIncidents($incidents);


        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zones'                     => $this->entityManagerFacade->getZones(),
            'incidentCategories'        => $this->entityManagerFacade->getIncidentCategories(),
            'zone'                      => $zone,
            'zoneProductLines'          => $productLines,
        ]);
    }



    // Creation of new productLine
    #[Route('/create_productline/{zoneId}', name: 'admin_create_productline')]
    public function createProductLine(Request $request, ?int $zoneId = null): Response
    {

        if (!preg_match("/^[^.]+$/", $request->request->get('productLineName'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
        } else {

            $zone = $this->entityManagerFacade->find('zone', $zoneId);
            // Check if the productLine already exists by comparing the productLine name and the zone
            $productLineName = $request->request->get('productLineName') . '.' . $zone->getName();
            $productLine = $this->entityManagerFacade->findOneBy('productLine', ['name' => $productLineName]);

            if ($productLine) {
                $this->addFlash('danger', 'La ligne de produit existe déjà');
                return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
            } else {
                $count = $this->entityManagerFacade->count('productLine', ['zone' => $zoneId]);
                $sortOrder = $count + 1;

                $productLine = new ProductLine();
                $productLine->setName($productLineName);
                $productLine->setZone($zone);
                $productLine->setSortOrder($sortOrder);
                $productLine->setCreator($this->getUser());

                $em = $this->entityManagerFacade->getEntityManager();
                $em->persist($productLine);
                $em->flush();

                $this->contentManagerFacade->folderStructure($productLineName);

                $this->addFlash('success', 'The Product Line has been created');
                return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
            }
        }
    }


    // Delete a productLine and all its children entities, it depends on the entitydeletionService
    #[Route('/delete_productline/{productLineId}', name: 'admin_delete_productline')]
    public function deleteEntityProductLine(int $productLineId): Response
    {
        $entityType = 'productLine';
        $productLine = $this->entityManagerFacade->find('productLine', $productLineId);

        $zoneId = $productLine->getZone()->getId();

        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN")) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class.
            // The folder is deleted by the FolderService class through the EntityDeletionService class.
            $response = $this->entityManagerFacade->deleteEntity($entityType, $productLine->getId());
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ligne.');
            return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
        }
        if ($response) {
            $this->addFlash('success', 'La ligne de produit ' . $productLine->getName() . ' a été supprimée');
            return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
        } else {
            $this->addFlash('danger', 'La ligne de produit ' . $productLine->getName() . ' n\'existe pas');
            return $this->redirectToRoute('app_zone_admin', ['zoneId' => $zoneId]);
        }
    }
}
