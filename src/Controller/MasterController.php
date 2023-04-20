<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


use App\Service\AccountService;

use App\Entity\Zone;

class MasterController extends BaseController
{

    #[Route('/master', name: 'app_master')]

    public function index(AuthenticationUtils $authenticationUtils,): Response
    {
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('master/master_index.html.twig', [
            'controller_name' => 'MasterController',
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),

            'error' => $error,
            'last_username' => $lastUsername,
            'zones' => $this->zoneRepository->findAll(),
            'users' => $this->userRepository->findAll()
        ]);
    }

    #[Route('/master/create_admin', name: 'app_master_create_admin')]

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

        return $this->redirectToRoute('app_login');
    }



    #[Route('/master/create_zone', name: 'app_master_create_zone')]
    public function createZone(Request $request)
    {
        // Create a zone
        if ($request->getMethod() == 'POST') {
            $zonename = $request->request->get('zonename');


            $zone = $this->zoneRepository->findOneBy(['name' => $zonename]);
            if ($zone) {
                $this->addFlash('danger', 'Zone already exists');
                return $this->redirectToRoute('app_master', [
                    'controller_name' => 'MasterController',
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            } else {
                $zone = new Zone();
                $zone->setName($zonename);
                $this->em->persist($zone);
                $this->em->flush();
                $this->addFlash('success', 'Zone has been created');
                return $this->redirectToRoute('app_master', [
                    'controller_name' => 'MasterController',
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            }
        }
    }

    #[Route('/master/delete_zone/{id}', name: 'app_master_delete_zone')]
    public function deleteEntity(string $id): Response
    {
        $entityType = 'zone';
        $entityid = $this->zoneRepository->findOneBy(['name' => $id]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_master');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_master');
        }
    }
}