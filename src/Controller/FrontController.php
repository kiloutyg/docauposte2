<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;






#[Route('/', name: 'app_')]



class FrontController extends BaseController
{
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'zones'        => $this->zoneRepository->findAll(),
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
                'users'        => $this->userRepository->findAll(),
                'documents'    => $this->documentRepository->findAll(),
            ]
        );
    }

    #[Route('/zone/{id}', name: 'zone')]

    public function zone(int $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['id' => $id]);

        return $this->render(
            'zone.html.twig',
            [

                'zone'         => $zone,
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
            ]
        );
    }



    #[Route('/productline/{id}', name: 'productline')]
    public function productline(int $id = null): Response
    {

        $productLine = $this->productLineRepository->findoneBy(['id' => $id]);
        return $this->render(
            'productline.html.twig',
            [
                'zones'       => $this->zoneRepository->findAll(),

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }



}