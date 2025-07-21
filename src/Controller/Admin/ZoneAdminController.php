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


    /**
     * Renders the zone administration interface with zone details and product lines.
     *
     * This method displays the admin interface for a specific zone, including all zones
     * for navigation and the product lines associated with the current zone. If no zone
     * is found, it redirects to an error page.
     *
     * @param int|null $zoneId The ID of the zone to display. Can be null if zone object is provided.
     * @param Zone|null $zone The zone entity object. If null, will be fetched using zoneId.
     *
     * @return Response The rendered admin template with zone data or error redirect response.
     */
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

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'zones'                     => $this->entityManagerFacade->getZones(),
            'zone'                      => $zone,
            'zoneProductLines'          => $zone->getProductLines(),
        ]);
    }




    /**
     * Creates a new product line within a specified zone.
     *
     * This method handles the creation of a new product line by validating the input,
     * checking for duplicates, and persisting the new entity to the database. It also
     * creates the corresponding folder structure and provides user feedback through
     * flash messages.
     *
     * @param Request $request The HTTP request object containing the product line name in POST data
     * @param int|null $zoneId The ID of the zone where the product line will be created. Can be null.
     *
     * @return Response A redirect response to the zone admin page with appropriate flash messages
     */
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
    /**
     * Deletes a product line and all its associated child entities.
     *
     * This method handles the deletion of a product line by first checking if the current user
     * has the required permissions (ROLE_LINE_ADMIN). If authorized, it deletes the product line
     * and all its related entities through the EntityDeletionService. The corresponding folder
     * structure is also removed. Appropriate flash messages are displayed based on the operation
     * result, and the user is redirected back to the zone admin page.
     *
     * @param int $productLineId The unique identifier of the product line to be deleted
     *
     * @return Response A redirect response to the zone admin page with success, error, or danger flash messages
     */
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
