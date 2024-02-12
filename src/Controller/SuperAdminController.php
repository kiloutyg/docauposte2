<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\Zone;
use App\Entity\Team;
use App\Entity\Uap;

use App\Form\TeamType;
use App\Form\UapType;

// This controller is responsible for rendering the super admin interface an managing the logic of the super admin interface
class SuperAdminController extends FrontController
{

    // This function is responsible for rendering the super admin interface
    #[Route('/super_admin', name: 'app_super_admin')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $incidents = $this->incidents;
        $uploads = $this->uploads;

        // Group the uploads and incidents by parent entity
        $groupedUploads = $this->uploadService->groupUploads($uploads);
        $groupedValidatedUploads = $this->uploadService->groupValidatedUploads($uploads);
        $groupIncidents = $this->incidentService->groupIncidents($incidents);

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('super_admin/super_admin_index.html.twig', [
            'groupedUploads'        => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'        => $groupIncidents,
            'error'                 => $error,
            'last_username'         => $lastUsername
        ]);
    }

    // Creation of new user account destined to the super admin
    #[Route('/super_admin/create_admin', name: 'app_super_admin_create_admin')]
    public function createAdmin(Request $request): Response
    {
        $error = null;
        try {
            $result = $this->accountService->createAccount($request);
            if ($result) {
                $this->addFlash('success', 'Le compte a bien été créé.');
            }
        } catch (\Exception $e) {
            // Catch and handle the exception.
            // Log it, add a flash message, etc.
            $error = $e->getMessage();
            $this->addFlash('danger', $error);
        }

        return $this->redirectToRoute('app_super_admin', [
            'error'         => $error
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
                $this->addFlash('success', 'La zone a été créee');
                return $this->redirectToRoute('app_super_admin');
            }
        }
    }

    // Zone deletion logic destined to the super admin, it also deletes the folder structure for the zone
    #[Route('/super_admin/delete_zone/{zoneId}', name: 'app_super_admin_delete_zone')]
    public function deleteEntity(int $zoneId): Response
    {
        $entityType = 'zone';
        $entity = $this->zoneRepository->findOneBy(['id' => $zoneId]);

        $response = $this->entitydeletionService->deleteEntity($entityType, $entity->getId());

        if ($response == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }

    #[Route('/super_admin/operator_management', name: 'app_super_admin_operator_management')]
    public function operatorManagement(Request $request): Response
    {
        $team = new Team();
        $uap = new Uap();
        $teamForm = $this->createForm(TeamType::class, $team);
        $uapForm = $this->createForm(UapType::class, $uap);

        $originUrl = $this->request->headers->get('referer');

        if ($request->getMethod() == 'POST') {
            $teamForm->handleRequest($request);
            $uapForm->handleRequest($request);
            if ($teamForm->isSubmitted()) {
                if ($teamForm->isValid()) {
                    $team = $teamForm->getData();
                    $this->em->persist($team);
                    $this->em->flush();
                    $this->addFlash('success', 'Team has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageTeam = $teamForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageTeam);
                    return $this->redirect($originUrl);
                }
            }
            if ($uapForm->isSubmitted()) {
                if ($uapForm->isValid()) {
                    $uap = $uapForm->getData();
                    $this->em->persist($uap);
                    $this->em->flush();
                    $this->addFlash('success', 'Uap has been created');
                    return $this->redirect($originUrl);
                } else {
                    // Validation failed, get the error message and display it
                    $errorMessageUap = $uapForm->getErrors(true)->current()->getMessage();
                    $this->addFlash('danger', $errorMessageUap);
                    return $this->redirect($originUrl);
                }
            }
        } else if ($request->getMethod() == 'GET') {
            return $this->render('services/operators/operator_management.html.twig', [
                'teamForm' => $teamForm->createView(),
                'uapForm' => $uapForm->createView()
            ]);
        }
    }
}
