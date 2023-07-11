<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


use App\Service\AccountService;
use App\Service\UploadsService;
use App\Service\IncidentsService;


use App\Entity\ProductLine;

class ZoneAdminController extends BaseController

{

    #[Route('/zone_admin/{zone}', name: 'app_zone_admin')]

    public function index(IncidentsService $incidentsService, UploadsService $uploadsService, AuthenticationUtils $authenticationUtils, string $zone = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);
        $uploads = $this->entityHeritanceService->uploadsByParentEntity('zone', $zone->getId());
        $incidents = $this->entityHeritanceService->incidentsByParentEntity('zone', $zone->getId());

        $groupedUploads = $uploadsService->groupUploads($uploads);
        $groupIncidents = $incidentsService->groupIncidents($incidents);



        return $this->render('zone_admin/zone_admin_index.html.twig', [
            'groupedUploads'    => $groupedUploads,
            'groupincidents'    => $groupIncidents,
            'zone'              => $zone,
            'productLines'      => $this->productLineRepository->findAll(),
            'buttons'           => $this->buttonRepository->findAll(),
            'uploads'           => $this->uploadRepository->findAll(),
            'users'             => $this->userRepository->findAll(),
            'incidents'         => $this->incidentRepository->findAll(),
            'incidentCategories' => $this->incidentCategoryRepository->findAll(),


        ]);
    }


    #[Route('/zone_admin/create_line_admin/{zone}', name: 'app_zone_admin_create_line_admin')]

    public function createLineAdmin(string $zone = null, AccountService $accountService, Request $request): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        $error = null;
        $result = $accountService->createAccount(
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
            'id'           => $zone->getId(),
            'productLines' => $this->productLineRepository->findAll(),
        ]);
    }


    #[Route('/zone_admin/create_productline/{zone}', name: 'app_zone_admin_create_productline')]
    public function createProductLine(Request $request, string $zone = null)
    {
        // 
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        if (!preg_match("/^[^.]+$/", $request->request->get('productlinename'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone->getName(),
                'productLines' => $this->productLineRepository->findAll(),
            ]);
        } else {
            // Create a productline

            $productlinename = $request->request->get('productlinename') . '.' . $zone->getName();
            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);

            if ($productline) {
                $this->addFlash('danger', 'La ligne de produit existe déjà');
                return $this->redirectToRoute('app_zone_admin', [
                    'zone' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            } else {
                $productline = new ProductLine();
                $productline->setName($productlinename);
                $productline->setZone($zone);
                $this->em->persist($productline);
                $this->em->flush();
                $this->folderCreationService->folderStructure($productlinename);
                $this->addFlash('success', 'The Product Line has been created');
                return $this->redirectToRoute('app_zone_admin', [
                    'zone' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            }
        }
    }

    #[Route('/zone_admin/delete_productline/{productline}', name: 'app_zone_admin_delete_productline')]
    public function deleteEntity(string $productline): Response
    {
        $entityType = 'productline';
        $entityid = $this->productLineRepository->findOneBy(['name' => $productline]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $zone = $entityid->getZone()->getName();

        if ($entity == true) {

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