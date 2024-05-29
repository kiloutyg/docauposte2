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
        $this->validationService->remindCheck($this->users);

        if ($this->authChecker->isGranted('ROLE_MANAGER')) {
            $countArray = $this->operatorService->operatorCheckForAutoDelete();
            if ($countArray != null) {
                $this->addFlash('info', ($countArray['inActiveOperators'] === 1 ? $countArray['inActiveOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['inActiveOperators'] . ' opérateurs inactifs sont à supprimer. ') .
                    ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
            }
        }

        return $this->render(
            'base.html.twig',
            [
                "zonesBase" => $this->zones,
                "zonesServices" => $this->cacheService->zones,
                "zoneRepo" => $this->zoneRepository->findAll(),
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
    #[Route('/zone/{zoneId}', name: 'zone')]
    public function zone(int $zoneId = null): Response
    {

        // $zone = $this->cacheService->zones->filter(function ($zone) use ($zoneId) {
        //     return $zone->getId() === $zoneId;
        // })->first();

        $zone = $this->cacheService->getEntityById('zone', $zoneId);

        return $this->render(
            'zone.html.twig',
            [
                'zone'         => $zone
            ]
        );
    }


    // Render the productline page and redirect to the mandatory incident page if there is one
    #[Route('/zone/{zoneId}/productline/{productlineId}', name: 'productline')]
    public function productline(int $zoneId = null, int $productlineId = null): Response
    {

        // $productLine = $this->productLineRepository->find($productlineId);
        // $zone        = $productLine->getZone();
        $productLine = $this->cacheService->getEntityById('productLine', $productlineId);

        $zone = $this->cacheService->getEntityById('zone', $zoneId);

        $incidents = [];
        $incidents = $this->incidentRepository->findBy(
            ['ProductLine' => $productlineId],
            ['id' => 'ASC'] // order by id ascending
        );

        $incidentId = count($incidents) > 0 ? $incidents[0]->getId() : null;

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
                'zoneId' => $zoneId,
                'productlineId' => $productlineId,
                'incidentId' => $incidentId
            ]);
        }
    }



    // Render the category page and redirect to the button page if there is only one button in the category
    #[Route('/zone/{zoneId}/productline/{productlineId}/category/{categoryId}', name: 'category')]

    public function category(int $categoryId = null): Response
    {
        $category    = $this->categoryRepository->find($categoryId);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $buttons = [];
        $buttons = $this->buttonRepository->findBy(['Category' => $categoryId]);

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
            $buttonId = $buttons[0]->getId();
            return $this->redirectToRoute('app_button', [
                'zoneId'        => $zone->getId(),
                'productlineId' => $productLine->getId(),
                'categoryId'    => $category->getId(),
                'buttonId'      => $buttonId
            ]);
        }
    }


    // Render the button page and redirect to the upload page if there is only one upload in the button
    #[Route('/zone/{zoneId}/productline/{productlineId}/category/{categoryId}/button/{buttonId}', name: 'button')]
    public function buttonDisplay(UploadController $uploadController, int $buttonId = null, Request $request): Response
    {
        $buttonEntity = $this->buttonRepository->find($buttonId);
        $category    = $buttonEntity->getCategory();
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();
        $uploads = [];

        $buttonUploads = $this->uploadRepository->findBy(['button' => $buttonId]);
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
            return $uploadController->downloadFile($uploadId, $request);
        }
    }
    #[Route('/flash-messages', name: 'flash_messages')]
    public function flashMessages(Request $request): Response
    {
        return $this->render('services/_toasts.html.twig');
    }
}
