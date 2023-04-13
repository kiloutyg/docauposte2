<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

use App\Controller\AdminController;
use App\Controller\SecurityController;
use App\Service\AccountService;
use App\Repository\ZoneRepository;
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
        $result = $accountService->createAccount($request, $error, 'app_base', []);

        if ($result) {
            $this->addFlash('success', 'Account has been created');
            return $this->redirectToRoute($result['route'], $result['params']);
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
                return $this->redirectToRoute('app_master');
            } else {
                $zone = new Zone();
                $zone->setName($zonename);
                $this->em->persist($zone);
                $this->em->flush();
                $this->addFlash('success', 'Zone has been created');
                return $this->redirectToRoute('app_master');
            }
        }
    }

    #[Route('/master/delete_zone/{id}', name: 'app_master_delete_zone')]
    public function deleteZone($id)
    {
        $zone = $this->zoneRepository->find($id);
        if ($zone) {
            $this->em->remove($zone);
            $this->em->flush();
            $this->addFlash('success', 'Zone has been deleted');
            return $this->redirectToRoute('app_master');
        } else {
            $this->addFlash('danger', 'Zone does not exist');
            return $this->redirectToRoute('app_master');
        }
    }
}