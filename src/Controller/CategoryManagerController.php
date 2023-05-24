<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\AccountService;

use App\Entity\Button;


class CategoryManagerController extends BaseController
{

    #[Route('/category_manager/{category}', name: 'app_category_manager')]

    public function index(AuthenticationUtils $authenticationUtils, string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('category_manager/category_manager_index.html.twig', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'productLine' => $productLine,
            'id'          => $productLine->getName(),
            'category'    => $category,
            'categories'  => $this->categoryRepository->findAll(),
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),
            'users' => $this->userRepository->findAll(),
            'error'         => $error,
            'last_username' => $lastUsername,
        ]);
    }



    #[Route('/category_manager/create_user/{category}', name: 'app_category_manager_create_user')]

    public function createUser(string $category = null, AccountService $accountService, Request $request): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);

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

        return $this->redirectToRoute('app_category_manager', [
            'category'    => $category->getName(),
        ]);
    }


    #[Route('/category_manager/create_button/{category}', name: 'app_category_manager_create_button')]

    public function createButton(Request $request, string $category = null)
    {
        $categoryentity    = $this->categoryRepository->findoneBy(['name' => $category]);
        // Check if button name does not contain the disallowed characters
        if (!preg_match("/^[^.]+$/", $request->request->get('buttonname'))) {
            // Handle the case when button name contains disallowed characters
            $this->addFlash('danger', 'Nom de boutton invalide');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {

            // Handle the case when button name does not contain disallowed characters
            // Create a button

            $buttonname = $request->request->get('buttonname') . '.' . $categoryentity->getName();

            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);

            if ($button) {
                $this->addFlash('danger', 'bouton already exists');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
            } else {
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($categoryentity);
                $this->em->persist($button);
                $this->em->flush();
                $this->folderCreationService->folderStructure($buttonname);

                $this->addFlash('success', 'The Button has been created');
                return $this->redirectToRoute('app_category_manager', [
                    'category'    => $category,
                ]);
            }
        }
    }

    #[Route('/category_manager/delete_button/{buttonid}', name: 'app_category_manager_delete_button')]
    public function deleteEntity(string $buttonid): Response
    {
        $entityType = 'button';
        $entityid = $this->buttonRepository->findOneBy(['name' => $buttonid]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $category = $entityid->getCategory()->getName();

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_category_manager', [
                'category'    => $category,
            ]);
        }
    }
}