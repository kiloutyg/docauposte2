<?php

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Upload;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Service\AccountService;



#[Route('/', name: 'app_')]
class FrontController extends BaseController
{
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'categories'  => $this->categoryRepository->findAll(),

                'zones'        => $this->zoneRepository->findAll(),
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
                'users'        => $this->userRepository->findAll(),
                'user'         => $this->getUser(),
            ]
        );
    }
    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(AccountService $accountService, Request $request): Response
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

        return $this->redirectToRoute('app_base');
    }




    #[Route('/zone/{id}', name: 'zone')]
    public function zone(string $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $id]);

        return $this->render(
            'zone.html.twig',
            [

                'zone'         => $zone,
                'productLines' => $this->productLineRepository->findAll(),
                'roles'        => $this->roleRepository->findAll(),
            ]
        );
    }



    #[Route('/zone/{name}/productline/{id}', name: 'productline')]
    public function productline(string $id = null): Response
    {

        $productLine = $this->productLineRepository->findoneBy(['name' => $id]);
        $zone        = $productLine->getZone();

        return $this->render(
            'productline.html.twig',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'uploads'     => $this->uploadRepository->findAll(),
                'id' => $productLine->getName(),
                'categories'  => $this->categoryRepository->findAll(),

                'productLine' => $productLine,

                'roles'       => $this->roleRepository->findAll(),
            ]
        );
    }




    #[Route('/zone/{name}/productline/{id}/category/{category}', name: 'category')]

    public function category(string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();


        return $this->render(
            'category.html.twig',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'productLine' => $productLine,
                'id'          => $productLine->getName(),
                'category'    => $category,
                'categories'  => $this->categoryRepository->findAll(),
                'buttons'     => $this->buttonRepository->findAll(),
            ]
        );
    }


    #[Route('/zone/{name}/productline/{id}/category/{category}/button', name: 'button')]
    public function ButtonShowingUploadedFile(string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();


        return $this->render(
            'uploads/uploaded.html.twig',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'productLine' => $productLine,
                'id'          => $productLine->getName(),
                'category'    => $category,
                'categories'  => $this->categoryRepository->findAll(),
                'buttons'     => $this->buttonRepository->findAll(),
                'uploads'     => $this->uploadRepository->findAll(),



            ]
        );
    }
}