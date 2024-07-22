<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\ProductLine;

// This controller is responsible for rendering the zone admin interface an managing the logic of the zone admin interface
class ZoneAdminController extends FrontController
{
    // This function is responsible for rendering the zone admin interface
    #[Route('/zone_admin/{zoneId}', name: 'app_zone_admin')]
    public function index(int $zoneId = null): Response
    {
        $pageLevel = 'zone';
        $zone = $this->cacheService->getEntityById('zone', $zoneId);
        $productLines = $this->cacheService->getEntitiesByParentId('productLine', $zoneId);

        $uploads = $this->entityHeritanceService->uploadsByParentEntity('zone', $zoneId);
        $incidents = $this->entityHeritanceService->incidentsByParentEntity('zone', $zoneId);

        // Group the uploads and incidents by parent entity
        $groupedUploads = $this->uploadService->groupUploads($uploads);
        $groupedValidatedUploads = $this->uploadService->groupValidatedUploads($uploads);
        $groupIncidents = $this->incidentService->groupIncidents($incidents);


        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'zoneProductLines'          => $productLines,
        ]);
    }



    // Creation of new user account destined to the zone admin but only accessible by the super admin
    #[Route('/zone_admin/create_line_admin/{zoneId}', name: 'app_zone_admin_create_line_admin')]
    public function createLineAdmin(int $zoneId = null, Request $request): Response
    {

        $error = null;
        $result = $this->accountService->createAccount(
            $request,
            $error
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_zone', [
            'zoneId'           => $zoneId
        ]);
    }


    // Creation of new productline
    #[Route('/zone_admin/create_productline/{zoneId}', name: 'app_zone_admin_create_productline')]
    public function createProductLine(Request $request, int $zoneId = null)
    {
        // 
        $zoneCached = $this->cacheService->getEntityById('zone', $zoneId);
        $zone = $this->zoneRepository->find($zoneCached);

        if (!preg_match("/^[^.]+$/", $request->request->get('productlinename'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->redirectToRoute('app_zone_admin', [
                'zoneId' => $zoneId
            ]);
        } else {
            // Check if the productline already exists by comparing the productline name and the zone
            $productlinename = $request->request->get('productlinename') . '.' . $zone->getName();
            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);

            if ($productline) {
                $this->addFlash('danger', 'La ligne de produit existe déjà');
                return $this->redirectToRoute('app_zone_admin', [
                    'zoneId' => $zoneId
                ]);
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
                return $this->redirectToRoute('app_zone_admin', [
                    'zoneId' => $zoneId
                ]);
            }
        }
    }


    // Delete a productline and all its children entities, it depends on the entitydeletionService
    #[Route('/zone_admin/delete_productline/{productlineId}', name: 'app_zone_admin_delete_productline')]
    public function deleteEntity(int $productlineId): Response
    {
        $entityType = 'productline';
        $entity = $this->productLineRepository->find($productlineId);
        $zoneId = $entity->getZone()->getId();

        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->authChecker->isGranted("ROLE_LINE_ADMIN") || $this->getUser() === $entity->getCreator()) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $entity->getId());
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ligne.');
            return $this->redirectToRoute('app_zone_admin', [
                'zoneId' => $zoneId,
            ]);
        }

        if ($response == true) {
            $this->addFlash('success', 'La ligne de produit ' . $entityType . ' a été supprimée');
            return $this->redirectToRoute('app_zone_admin', [
                'zoneId' => $zoneId,
            ]);
        } else {
            $this->addFlash('danger', 'La ligne de produit ' . $entityType . ' n\'existe pas');
            return $this->redirectToRoute('app_zone_admin', [
                'zoneId' => $zoneId,
            ]);
        }
    }
}
