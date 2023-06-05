<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\UploadsService;
use App\Service\AccountService;
use App\Service\IncidentsService;


use App\Entity\Zone;

class SuperAdminController extends BaseController
{

    #[Route('/super_admin', name: 'app_super_admin')]

    public function index(IncidentsService $incidentsService, UploadsService $uploadsService, AuthenticationUtils $authenticationUtils,): Response
    {
        $groupedUploads = $uploadsService->groupUploads();
        $groupIncidents = $incidentsService->groupIncidents();

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('super_admin/super_admin_index.html.twig', [
            'groupedUploads' => $groupedUploads,
            'groupincidents' => $groupIncidents,
            'error' => $error,
            'last_username' => $lastUsername,
            'zones' => $this->zoneRepository->findAll(),
            'productLines' => $this->productLineRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),
            'users' => $this->userRepository->findAll(),
            'incidents' => $this->incidentRepository->findAll(),
            'incidentCategories' => $this->incidentCategoryRepository->findAll(),

        ]);
    }


    #[Route('/super_admin/create_admin', name: 'app_super_admin_create_admin')]

    public function createAdmin(AccountService $accountService, Request $request): Response
    {
        $error = null;
        $result = $accountService->createAccount($request, $error);

        if ($result) {
            $this->addFlash('success', 'Account has been created');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_super_admin', [
            'buttons'       => $this->buttonRepository->findAll(),
            'uploads'       => $this->uploadRepository->findAll(),
            'error'         => $error,
            'zones'         => $this->zoneRepository->findAll(),
            'users'         => $this->userRepository->findAll()
        ]);
    }



    #[Route('/super_admin/create_zone', name: 'app_super_admin_create_zone')]
    public function createZone(Request $request)
    {
        // Create a zone
        if ($request->getMethod() == 'POST') {
            $zonename = trim($request->request->get('zonename'));
            $zone = $this->zoneRepository->findOneBy(['name' => $zonename]);
            if (empty($zonename)) {
                $this->addFlash('danger', 'Le nom de la Zone ne peut être vide');
                return $this->redirectToRoute('app_super_admin', [
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            }

            if (!file_exists($this->public_dir . '/doc/')) {
                mkdir($this->public_dir . '/doc/', 0777, true);
            }

            if ($zone) {
                $this->addFlash('danger', 'La zone existe déjà');
                return $this->redirectToRoute('app_super_admin', [
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            } else {
                $zone = new Zone();
                $zone->setName($zonename);
                $this->em->persist($zone);
                $this->em->flush();
                $this->folderCreationService->folderStructure($zonename);
                $this->addFlash('success', 'La zone a été créee');
                return $this->redirectToRoute('app_super_admin', [
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            }
        }
    }

    #[Route('/super_admin/delete_zone/{id}', name: 'app_super_admin_delete_zone')]
    public function deleteEntity(string $id): Response
    {
        $entityType = 'zone';
        $entityid = $this->zoneRepository->findOneBy(['name' => $id]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }
}
