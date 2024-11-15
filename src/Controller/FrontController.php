<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Controller\UploadController;

use App\Entity\Department;

// This controller manage the logic of the front interface, it is the main controller of the application and is responsible for rendering the front interface.
// It is also responsible for creating the super-admin account.

#[Route('/', name: 'app_')]
class FrontController extends BaseController
{

    #[Route('/cache', name: 'cache')]
    public function resetCache(Request $request): Response
    {
        // $this->clearAndRebuildCachesArrays();
        $this->cacheService->clearAndRebuildCaches();
        return $this->redirectToRoute('app_base');
    }




    // This function is responsible for creating the super-admin account at the first connection of the application.
    #[Route('/createSuperAdmin', name: 'create_super_admin')]
    public function createSuperAdmin(Request $request): Response
    {
        $users = [];
        $users  = $this->cacheService->users;

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



    // Render the base page
    #[Route('/', name: 'base')]
    public function base(): Response
    {

        if ($this->cacheService->settings->isUploadValidation() && $this->validationRepository->findAll() != null) {
            $this->validationService->remindCheck($this->cacheService->users);
        }

        if ($this->departmentRepository->findAll() == null) {
            $Department = new Department();
            $Department->setName('I.T.');
            $this->em->persist($Department);
            $this->em->flush();
        }

        if ($this->cacheService->settings->isTraining() && $this->authChecker->isGranted('ROLE_MANAGER')) {
            $countArray = $this->operatorService->operatorCheckForAutoDelete();
            if ($countArray != null) {
                $this->addFlash('info', ($countArray['findDeactivatedOperators'] === 1 ? $countArray['findDeactivatedOperators'] . ' opérateur inactif est à supprimer. ' : $countArray['findDeactivatedOperators'] . ' opérateurs inactifs sont à supprimer. ') .
                    ($countArray['toBeDeletedOperators'] === 1 ? $countArray['toBeDeletedOperators'] . ' opérateur inactif n\'a été supprimé. ' : $countArray['toBeDeletedOperators'] . ' opérateurs inactifs ont été supprimés. '));
            }
        }

        return $this->render(
            'base.html.twig'
        );
    }




    // Render the zone page
    #[Route('/zone/{zoneId}', name: 'zone')]
    public function zone(int $zoneId = null): Response
    {
        $zone = $this->zoneRepository->find($zoneId);
        $linesInZone = [];
        $linesInZone = $zone->getProductLines();

        if (count($linesInZone) > 1) {
            return $this->render(
                'zone.html.twig',
                [
                    'zone' => $zone,
                ]
            );
        } else {
            return $this->redirectToRoute(
                'app_productline',
                [
                    'productline' => $linesInZone[0]->getId()
                ]
            );
        }
    }


    // Render the productline page and redirect to the mandatory incident page if there is one
    #[Route('/productline/{productlineId}', name: 'productline')]
    public function productline(int $productlineId = null, ProductLine $productLine): Response
    {

        $productLine = $this->productLineRepository->find($productlineId);
        $categoriesInLine = $productLine->getCategories();
        $incidents = [];
        $incidents = $this->incidentRepository->findBy(
            ['ProductLine' => $productlineId],
            ['id' => 'ASC'] // order by id ascending
        );

        $incidentId = count($incidents) > 0 ? $incidents[0]->getId() : null;

        if (count($incidents) == 0 && count($categoriesInLine) > 1) {

            return $this->render(
                'productline.html.twig',
                [
                    'productLine' => $productLine
                ]
            );
        } elseif (count($incidents) == 0 && count($categoriesInLine) == 1) {
            return $this->redirectToRoute(
                'app_category',
                [
                    'categoryId' => $categoriesInLine[0]->getId()
                ]
            );
        } else {
            return $this->redirectToRoute('app_mandatory_incident', [
                'productlineId' => $productlineId,
                'incidentId' => $incidentId
            ]);
        }
    }




    // Render the category page and redirect to the button page if there is only one button in the category
    #[Route('/category/{categoryId}', name: 'category')]
    public function category(int $categoryId = null): Response
    {

        $buttons = [];
        $buttons = $this->buttonRepository->findBy(['Category' => $categoryId]);

        $category = $this->cacheService->getEntityById('category', $categoryId);

        if (count($buttons) > 1) {
            return $this->render(
                'category.html.twig',
                [
                    'category'    => $category,
                    'matchingButtons' => $buttons,
                ]
            );
        } else {
            return $this->redirectToRoute('app_button', [
                'buttonId' => $buttons[0]->getId()
            ]);
        }
    }




    // Render the button page and redirect to the upload page if there is only one upload in the button
    #[Route('/button/{buttonId}', name: 'button')]
    public function buttonDisplay(UploadController $uploadController, int $buttonId = null, Request $request): Response
    {
        $buttonEntity = $this->buttonRepository->find($buttonId);

        $buttonUploads = $this->uploadRepository->findBy(['button' => $buttonId]);
        // $this->logger->info('buttonUploads', [$buttonUploads]);

        if (count($buttonUploads) != 1) {
            return $this->render(
                'button.html.twig',
                [
                    'button'      => $buttonEntity,
                    'uploads'     => $buttonUploads,
                ]
            );
        } else {
            $uploadId = $buttonUploads[0]->getId();
            return $uploadController->filterDownloadFile($uploadId, $request);
        }
    }
}
