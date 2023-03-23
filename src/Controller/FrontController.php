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

    public function zone(string $id = null): Response
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



    // show a page with all the documents of a productline
    #[Route('/productline/{product_line_id}', name: 'productline')]
    public function productline(string $product_line_id = null): Response
    {
        $productline = $this->productLineRepository->findOneBy(['product_line_id' => $product_line_id]);

        return $this->render(
            'productline.html.twig',
            [

                'productline' => $productline,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }



}