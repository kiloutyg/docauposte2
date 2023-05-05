<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


use App\Service\AccountService;

use App\Entity\Zone;

class SuperAdminController extends BaseController
{

    #[Route('/super_admin', name: 'app_super_admin')]

    public function index(AuthenticationUtils $authenticationUtils,): Response
    {
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('super_admin/super_admin_index.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
            'zones' => $this->zoneRepository->findAll(),
            'productLines' => $this->productLineRepository->findAll(),
            'categories' => $this->categoryRepository->findAll(),
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),
            'users' => $this->userRepository->findAll()
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
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),
            'error' => $error,
            'zones' => $this->zoneRepository->findAll(),
            'users' => $this->userRepository->findAll()
        ]);
    }



    #[Route('/super_admin/create_zone', name: 'app_super_admin_create_zone')]
    public function createZone(Request $request)
    {
        // Create a zone
        if ($request->getMethod() == 'POST') {
            $zonename = $request->request->get('zonename');


            $zone = $this->zoneRepository->findOneBy(['name' => $zonename]);
            if ($zone) {
                $this->addFlash('danger', 'Zone already exists');
                return $this->redirectToRoute('app_super_admin', [
                    'zones' => $this->zoneRepository->findAll(),
                    'users' => $this->userRepository->findAll()
                ]);
            } else {
                $zone = new Zone();
                $zone->setName($zonename);
                $this->em->persist($zone);
                $this->em->flush();
                $this->addFlash('success', 'Zone has been created');
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