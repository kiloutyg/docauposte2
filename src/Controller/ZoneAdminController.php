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
    #[Route('/zone_admin/{zone}', name: 'app_zone_admin')]
    public function index(string $zone = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);
        $uploads = $this->entityHeritanceService->uploadsByParentEntity('zone', $zone->getId());
        $incidents = $this->entityHeritanceService->incidentsByParentEntity('zone', $zone->getId());

        // Group the uploads and incidents by parent entity
        $groupedUploads = $this->uploadService->groupUploads($uploads);
        $groupedValidatedUploads = $this->uploadService->groupValidatedUploads($uploads);
        $groupIncidents = $this->incidentService->groupIncidents($incidents);

        return $this->render('zone_admin/zone_admin_index.html.twig', [
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone
        ]);
    }


    // Creation of new user account destined to the zone admin but only accessible by the super admin
    #[Route('/zone_admin/create_line_admin/{zone}', name: 'app_zone_admin_create_line_admin')]
    public function createLineAdmin(string $zone = null, Request $request): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        $error = null;
        $result = $this->accountService->createAccount(
            $request,
            $error,
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_zone', [
            'zone'         => $zone->getName(),
            'id'           => $zone->getId()
        ]);
    }


    // Creation of new productline
    #[Route('/zone_admin/create_productline/{zone}', name: 'app_zone_admin_create_productline')]
    public function createProductLine(Request $request, string $zone = null)
    {
        // 
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        if (!preg_match("/^[^.]+$/", $request->request->get('productlinename'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone->getName()
            ]);
        } else {
            // Check if the productline already exists by comparing the productline name and the zone
            $productlinename = $request->request->get('productlinename') . '.' . $zone->getName();
            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);

            if ($productline) {
                $this->addFlash('danger', 'La ligne de produit existe déjà');
                return $this->redirectToRoute('app_zone_admin', [
                    'zone' => $zone->getName()
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
                    'zone' => $zone->getName()
                ]);
            }
        }
    }


    // Delete a productline and all its children entities, it depends on the entitydeletionService
    #[Route('/zone_admin/delete_productline/{productline}', name: 'app_zone_admin_delete_productline')]
    public function deleteEntity(string $productline): Response
    {
        $entityType = 'productline';
        $entity = $this->productLineRepository->findOneBy(['name' => $productline]);
        $zone = $entity->getZone()->getName();

        // Check if the user is the creator of the entity or if he is a super admin
        if ($this->getUser()->getRoles() != ["ROLE_SUPER_ADMIN"] || $this->getUser() === $entity->getCreator()) {
            // This function is used to delete a category and all the infants entity attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $entity->getId());
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ligne.');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone,
            ]);
        }

        if ($response == true) {
            $this->addFlash('success', 'La ligne de produit ' . $entityType . ' a été supprimée');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone,
            ]);
        } else {
            $this->addFlash('danger', 'La ligne de produit ' . $entityType . ' n\'existe pas');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone,
            ]);
        }
    }
}