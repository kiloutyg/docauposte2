<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Controller\UploadController;

// This controller manage the logic of the front interface, it is the main controller of the application and is responsible for rendering the front interface.
// It is also responsible for creating the super-admin account.

#[Route('/', name: 'app_')]
class FrontController extends BaseController
{
    // Render the base page
    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render(
            'base.html.twig',
            [
                'user'                  => $this->getUser()
            ]
        );
    }


    // This function is responsible for creating the super-admin account at the first connection of the application.
    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(Request $request): Response
    {
        $users = [];
        $users  = $this->users;

        if ($users == null) {

            $error = null;
            $result = $this->accountService->createAccount(
                $request,
                $error
            );
            if ($result) {
                $this->addFlash('success', 'Le compte de Super-Administrateur a bien été créé.');
            }
            if ($error) {
                $this->addFlash('error', $error);
            }
        } else {
            $this->addFlash('alert', 'Le compte de Super-Administrateur existe déjà.');
            return $this->redirectToRoute('app_base');
        }
        return $this->redirectToRoute('app_base');
    }


    // Render the zone page
    #[Route('/zone/{zone}', name: 'zone')]
    public function zone(string $zone = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

        return $this->render(
            'zone.html.twig',
            [
                'zone'         => $zone
            ]
        );
    }


    // Render the productline page and redirect to the mandatory incident page if there is one
    #[Route('/zone/{zone}/productline/{productline}', name: 'productline')]
    public function productline(string $productline = null): Response
    {

        $productLine = $this->productLineRepository->findoneBy(['name' => $productline]);
        $zone        = $productLine->getZone();

        $incidents = [];
        $incidents = $this->incidentRepository->findBy(
            ['ProductLine' => $productLine->getId()],
            ['id' => 'ASC'] // order by id ascending
        );

        $incidentid = count($incidents) > 0 ? $incidents[0]->getId() : null;

        if (count($incidents) == 0) {

            return $this->render(
                'productline.html.twig',
                [
                    'zone'        => $zone,
                    'productLine' => $productLine
                ]
            );
        } else {
            return $this->redirectToRoute('app_mandatory_incident', [
                'zone' => $zone->getName(),
                'productline' => $productLine->getName(),
                'incidentid' => $incidentid
            ]);
        }
    }



    // Render the category page and redirect to the button page if there is only one button in the category
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
                    'productLine' => $productLine,
                    'category'    => $category
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


    // Render the button page and redirect to the upload page if there is only one upload in the button
    #[Route('/zone/{zone}/productline/{productline}/category/{category}/button/{button}', name: 'button')]
    public function ButtonShowing(UploadController $uploadController, string $button = null, Request $request): Response
    {
        $buttonEntity = $this->buttonRepository->findOneBy(['name' => $button]);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $uploads = [];

        $buttonUploads = $this->uploadRepository->findBy(['button' => $buttonEntity->getId()]);
        foreach ($buttonUploads as $buttonUpload) {
            if ($buttonUpload->isValidated() || $buttonUpload->getOldUpload() != null) {
                $uploads[] = $buttonUpload;
            }
        }

        if (count($uploads) != 1) {
            return $this->render(
                'button.html.twig',
                [
                    'zone'        => $zone,
                    'productLine' => $productLine,
                    'category'    => $category,
                    'button'      => $buttonEntity,
                    'uploads'     => $uploads,
                ]
            );
        } else {
            $uploadId = $uploads[0]->getId();
            return $uploadController->download_file($uploadId, $request);
        }
    }
}