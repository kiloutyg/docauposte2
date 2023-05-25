<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Service\AccountService;

use App\Controller\UploadController;


#[Route('/', name: 'app_')]
class FrontController extends BaseController
{
    // Page d'accueil
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'categories'  => $this->categoryRepository->findAll(),
                'buttons' => $this->buttonRepository->findAll(),
                'zones'        => $this->zoneRepository->findAll(),
                'productLines' => $this->productLineRepository->findAll(),
                'users'        => $this->userRepository->findAll(),
                'user'         => $this->getUser(),
            ]
        );
    }


    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(AccountService $accountService, Request $request): Response
    {
        $error = null;
        $result = $accountService->createAccount(
            $request,
            $error,
        );
        if ($result) {
            $this->addFlash('success', 'Account has been created');
        }
        if ($error) {
            $this->addFlash('error', $error);
        }
        return $this->redirectToRoute('app_base');
    }


    // Page de zone
    #[Route('/zone/{zone}', name: 'zone')]
    public function zone(string $zone = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        return $this->render(
            'zone.html.twig',
            [
                'zone'         => $zone,
                'productLines' => $this->productLineRepository->findAll(),
            ]
        );
    }


    // Page de productline
    #[Route('/zone/{zone}/productline/{productline}', name: 'productline')]
    public function productline(string $productline = null): Response
    {

        $productLine = $this->productLineRepository->findoneBy(['name' => $productline]);
        $zone        = $productLine->getZone();
        return $this->render(
            'productline.html.twig',
            [
                'zone'        => $zone,
                'name'        => $zone->getName(),
                'uploads'     => $this->uploadRepository->findAll(),
                'id'          => $productLine->getName(),
                'categories'  => $this->categoryRepository->findAll(),
                'productLine' => $productLine,
            ]
        );
    }


    // Page de category
    #[Route('/zone/{zone}/productline/{productline}/category/{category}', name: 'category')]

    public function category(string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $buttons = [];
        $buttons = $this->buttonRepository->findBy(['Category' => $category->getId()]);

        if (count($buttons) != 1) {

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
        } else {
            $button = $buttons[0]->getName();
            return $this->redirectToRoute('app_button', [
                'zone' => $zone->getName(),
                'productline' => $productLine->getName(),
                'category' => $category->getName(),
                'button' => $button
            ]);
        }
    }


    #[Route('/zone/{zone}/productline/{productline}/category/{category}/button/{button}', name: 'button')]
    public function ButtonShowing(UploadController $uploadController, string $button = null): Response
    {
        $buttonEntity = $this->buttonRepository->findoneBy(['name' => $button]);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $uploads = [];
        $uploads = $this->uploadRepository->findBy(['button' => $buttonEntity->getId()]);

        if (count($uploads) != 1) {

            return $this->render(
                'button.html.twig',
                [
                    'zone'        => $zone,
                    'name'        => $zone->getName(),
                    'productLine' => $productLine,
                    'id'          => $productLine->getName(),
                    'category'    => $buttonEntity->getName(),
                    'categories'  => $this->categoryRepository->findAll(),
                    'button'      => $buttonEntity,
                    'uploads'     => $this->uploadRepository->findAll(),

                ]
            );
        } else {
            $filename = $uploads[0]->getFilename();
            return $uploadController->download_file($filename);
        }
    }
}