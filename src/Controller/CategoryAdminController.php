<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Button;

// This controller manage the logic of the category's admin interface

class CategoryAdminController extends FrontController
{

    #[Route('/category_admin/{categoryId}', name: 'app_category_admin')]

    // This function is responsible for rendering the category's admin interface. 
    public function index(int $categoryId = null): Response
    {
        $pageLevel = 'category';

        $category    = $this->cacheService->getEntityById('category', $categoryId);
        $buttons     = $this->cacheService->getEntitiesByParentId('button', $categoryId);
        $productLine = $this->cacheService->getEntityById('productLine', $category->getProductLine()->getId());
        $zone        = $this->cacheService->getEntityById('zone', $productLine->getZone()->getId());

        // These functions are responsible for retrieving the uploads and incidents children of the current category, it depends on the EntityHeritanceService class.
        $uploads = $this->entityHeritanceService->uploadsByParentEntity(
            'category',
            $category->getId()
        );
        $incidents = $this->entityHeritanceService->incidentsByParentEntity(
            'category',
            $category->getId()
        );

        // These functions are responsible for grouping the uploads and incidents by button and parent entity, it depends on the UploadService and IncidentService classes.
        $groupedUploads = $this->uploadService->groupUploads($uploads);
        $groupIncidents = $this->incidentService->groupIncidents($incidents);
        $groupedValidatedUploads = $this->uploadService->groupValidatedUploads($uploads);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents'            => $groupIncidents,
            'zone'                      => $zone,
            'productLine'               => $productLine,
            'category'                  => $category,
            'uploads'                   => $uploads,
            'incidents'                 => $incidents,
            'categoryButtons'           => $buttons

        ]);
    }



    #[Route('/category_admin/create_user/{categoryId}', name: 'app_category_admin_create_user')]

    // This function is responsible for creating a new user, it's access is restricted on the frontend
    public function createUser(int $categoryId = null, Request $request): Response
    {
        $category = $this->categoryRepository->find($categoryId);

        $error = null;
        // This function is responsible for creating a new user, it depends on the AccountService class.
        $result = $this->accountService->createAccount(
            $request,
            $error
        );

        if ($result) {
            $this->addFlash('success', 'Le compte a été créé');
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_category_admin', [
            'categoryId'    => $categoryId,
        ]);
    }


    #[Route('/category_admin/create_button/{categoryId}', name: 'app_category_admin_create_button')]

    // This function is used to create a new button to which is attached the uploads.
    public function createButton(Request $request, int $categoryId = null)
    {
        $categoryentity = $this->categoryRepository->find($categoryId);
        // Check if button name does not contain the disallowed characters
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {

            // Handle the case when button name contains disallowed characters
            $this->addFlash('danger', 'Nom de bouton invalide');
            return $this->redirectToRoute('app_category_admin', [
                'categoryId'    => $categoryId,
            ]);
        } else {
            // Look if the the button already exists
            $buttonname = $request->request->get('buttonname') . '.' . $categoryentity->getName();
            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);

            // If the button already exists, redirect to the category manager page and display a flash message.
            if ($button) {
                $this->addFlash('danger', 'Le bouton existe déjà');
                return $this->redirectToRoute('app_category_admin', [
                    'categoryId'    => $categoryId,
                ]);
                // If the button does not exist, create it and redirect to the category manager page and display a flash message. It depends on the FolderCreationService class.
            } else {
                $count = $this->buttonRepository->count(['Category' => $categoryentity->getId()]);
                $sortOrder = $count + 1;
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($categoryentity);
                $button->setSortOrder($sortOrder);
                $button->setCreator($this->getUser());
                $this->em->persist($button);
                $this->em->flush();
                $this->folderCreationService->folderStructure($buttonname);

                $this->addFlash('success', 'Le bouton a été créé');
                return $this->redirectToRoute('app_category_admin', [
                    'categoryId'    => $categoryId,
                ]);
            }
        }
    }

    #[Route('/category_admin/delete_button/{buttonId}', name: 'app_category_admin_delete_button')]

    // This function is used to delete a button and all the uploads attached to it.
    public function deleteEntity(int $buttonId): Response
    {
        $entityType = 'button';
        $entity = $this->buttonRepository->find($buttonId);
        $categoryId = $entity->getCategory()->getId();

        // Check if the user is the creator of the button or if he is a super admin
        if ($this->getUser() === $entity->getCreator() || $this->authChecker->isGranted("ROLE_LINE_ADMIN")) {
            // This function is used to delete a button and all the uploads attached to it, it depends on the EntityDeletionService class. 
            // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
            $response = $this->entitydeletionService->deleteEntity($entityType, $buttonId);
        } else {
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer cette ' . $entityType . '.');
            return $this->redirectToRoute('app_category_admin', [
                'categoryId'    => $categoryId,
            ]);
        }

        if ($response == true) {
            $this->addFlash('success', 'Le bouton ' . $entityType . ' a été supprimé');
            return $this->redirectToRoute('app_category_admin', [
                'categoryId'    => $categoryId,
            ]);
        } else {
            $this->addFlash('danger', 'Le bouton ' . $entityType . ' n\'existe pas.');
            return $this->redirectToRoute('app_category_admin', [
                'categoryId'    => $categoryId,
            ]);
        }
    }
}
