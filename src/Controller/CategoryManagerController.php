<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Button;

// This controller manage the logic of the category's admin interface

class CategoryManagerController extends FrontController
{

    #[Route('/category_manager/{category}', name: 'app_category_manager')]

    // This function is responsible for rendering the category's admin interface. 
    public function index(string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

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

        return $this->render('category_manager/category_manager_index.html.twig', [
            'groupedUploads'        => $groupedUploads,
            'groupincidents'        => $groupIncidents,
            'zone'                  => $zone,
            'productLine'           => $productLine,
            'category'              => $category,
            'uploads'               => $uploads,
            'incidents'             => $incidents,

        ]);
    }



    #[Route('/category_manager/create_user/{category}', name: 'app_category_manager_create_user')]

    // This function is responsible for creating a new user, it's access is restricted on the frontend
    public function createUser(string $category = null, Request $request): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);

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

        return $this->redirectToRoute('app_category_manager', [
            'category'    => $category->getName(),
        ]);
    }


    #[Route('/category_manager/create_button/{category}', name: 'app_category_manager_create_button')]

    // This function is used to create a new button to which is attached the uploads.
    public function createButton(Request $request, string $category = null)
    {
        $categoryentity    = $this->categoryRepository->findoneBy(['name' => $category]);

        // Check if button name does not contain the disallowed characters
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {

            // Handle the case when button name contains disallowed characters
            $this->addFlash('danger', 'Nom de bouton invalide');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {
            // Look if the the button already exists
            $buttonname = $request->request->get('buttonname') . '.' . $categoryentity->getName();
            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);

            // If the button already exists, redirect to the category manager page and display a flash message.
            if ($button) {
                $this->addFlash('danger', 'Le bouton existe déjà');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
                // If the button does not exist, create it and redirect to the category manager page and display a flash message. It depends on the FolderCreationService class.
            } else {
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($categoryentity);
                $this->em->persist($button);
                $this->em->flush();
                $this->folderCreationService->folderStructure($buttonname);

                $this->addFlash('success', 'Le bouton a été créé');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
            }
        }
    }

    #[Route('/category_manager/delete_button/{button}', name: 'app_category_manager_delete_button')]

    // This function is used to delete a button and all the uploads attached to it.
    public function deleteEntity(string $button): Response
    {
        $entityType = 'button';
        $entityid = $this->buttonRepository->findOneBy(['name' => $button]);

        // This function is used to delete a button and all the uploads attached to it, it depends on the EntityDeletionService class. 
        // The folder is deleted by the FolderCreationService class through the EntityDeletionService class.
        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $category = $entityid->getCategory()->getName();

        if ($entity == true) {

            $this->addFlash('success', 'Le bouton ' . $entityType . ' a été supprimé');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {
            $this->addFlash('danger', 'Le bouton ' . $entityType . ' n\'existe pas.');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        }
    }
}