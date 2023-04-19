<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Service\AccountService;

use App\Entity\Button;


class ManagerController extends BaseController
{

    #[Route('/manager/{category}', name: 'app_manager')]

    public function index(AuthenticationUtils $authenticationUtils, string $category = null): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('manager/manager_index.html.twig', [
            'controller_name' => 'managerController',
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



    #[Route('/manager/create_user/{category}', name: 'app_manager_create_user')]

    public function createUser(string $category = null, AccountService $accountService, Request $request): Response
    {
        $category    = $this->categoryRepository->findoneBy(['name' => $category]);
        $productLine = $category->getProductLine();
        $zone        = $productLine->getZone();

        $error = null;
        $result = $accountService->createAccount(
            $request,
            $error,
            //  'app_productline', [
            //     'zone'        => $zone,
            //     'name'        => $zone->getName(),
            //     'uploads'     => $this->uploadRepository->findAll(),
            //     'id'          => $productLine->getName(),
            //     'categories'  => $this->categoryRepository->findAll(),
            //     'productLine' => $productLine,
            // ]
        );

        if ($result) {
            $this->addFlash('success', 'Account has been created');
            // return $this->redirectToRoute($result['route'], $result['params']);
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_category', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'productLine' => $productLine,
            'id'          => $productLine->getName(),
            'category'    => $category,
            'categories'  => $this->categoryRepository->findAll(),
            'buttons'     => $this->buttonRepository->findAll(),
        ]);
    }


    #[Route('/manager/create_button/{category}', name: 'app_manager_create_button')]

    public function createButton(Request $request, string $category = null)
    {
        $categoryentity    = $this->categoryRepository->findoneBy(['name' => $category]);

        // Create a button
        if ($request->getMethod() == 'POST') {

            $buttonname = $request->request->get('buttonname');

            $categoryentity   = $this->categoryRepository->findoneBy(['name' => $category]);

            $button = $this->buttonRepository->findoneBy(['name' => $buttonname]);
            if ($button) {
                $this->addFlash('danger', 'bouton already exists');
                return $this->redirectToRoute('app_manager', [
                    'controller_name'   => 'managerController',
                    'category'    => $category,
                    'buttons'     => $this->buttonRepository->findAll(),
                    'uploads'     => $this->uploadRepository->findAll(),
                ]);
            } else {
                $button = new Button();
                $button->setName($buttonname);
                $button->setCategory($categoryentity);
                $this->em->persist($button);
                $this->em->flush();
                $this->addFlash('success', 'The Button has been created');
                return $this->redirectToRoute('app_manager', [
                    'controller_name'   => 'managerController',
                    'category'    => $category,
                    'buttons'     => $this->buttonRepository->findAll(),
                    'uploads'     => $this->uploadRepository->findAll(),
                ]);
            }
        }
    }
    #[Route('/manager/delete_button/{id}', name: 'app_manager_delete_button')]
    public function deleteEntity(string $id): Response
    {
        $entityType = 'button';
        $entityid = $this->buttonRepository->findOneBy(['name' => $id]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $category = $entityid->getCategory()->getName();

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_manager', [
                'category'    => $category,
            ]);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_manager', [
                'category'    => $category,
            ]);
        }
    }
}