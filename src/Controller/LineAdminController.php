<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\AccountService;

use App\Controller\SecurityController;

use App\Repository\CategoryRepository;
use App\Repository\ProductLineRepository;

use App\Entity\ProductLine;
use App\Entity\Category;

class LineAdminController extends BaseController
{

    #[Route('/lineadmin/{id}', name: 'app_line_admin')]

    public function index(AuthenticationUtils $authenticationUtils, string $id = null): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productLine->getZone();

        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('line_admin/line_admin_index.html.twig', [
            'controller_name' => 'LineAdminController',
            'zone'          => $zone,
            'name'          => $zone->getName(),
            'productLine'   => $productLine,
            'id'            => $productLine->getName(),
            'categories'    => $this->categoryRepository->findAll(),
            'error'         => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/lineadmin/create_manager/{id}', name: 'app_line_admin_create_manager')]


    public function createManager(string $id = null, AccountService $accountService, Request $request): Response
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productLine->getZone();

        $error = null;
        $result = $accountService->createAccount($request, $error, 'app_productline', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'uploads'     => $this->uploadRepository->findAll(),
            'id'          => $productLine->getName(),
            'categories'  => $this->categoryRepository->findAll(),
            'productLine' => $productLine,
        ]);

        if ($result) {
            $this->addFlash('success', 'Account has been created');
            return $this->redirectToRoute($result['route'], $result['params']);
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_productline', [
            'zone'        => $zone,
            'name'        => $zone->getName(),
            'uploads'     => $this->uploadRepository->findAll(),
            'id'          => $productLine->getName(),
            'categories'  => $this->categoryRepository->findAll(),
            'productLine' => $productLine,
        ]);
    }

    #[Route('/lineadmin/create_category/{id}', name: 'app_line_admin_create_category')]
    public function createCategory(Request $request, string $id = null)
    {
        $productLine = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productLine->getZone();

        // Create a category
        if ($request->getMethod() == 'POST') {

            $categoryname = $request->request->get('categoryname');

            $productLine = $this->productLineRepository->findOneBy(['name' => $id]);

            $category = $this->categoryRepository->findOneBy(['name' => $categoryname]);
            if ($category) {
                $this->addFlash('danger', 'Category already exists');
                return $this->redirectToRoute('app_line_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'id'            => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),

                ]);
            } else {
                $category = new Category();
                $category->setName($categoryname);
                $category->setProductLine($productLine);
                $this->em->persist($category);
                $this->em->flush();
                $this->addFlash('success', 'The Category has been created');
                return $this->redirectToRoute('app_line_admin', [
                    'controller_name'   => 'LineAdminController',
                    'zone'          => $zone,
                    'name'          => $zone->getName(),
                    'productLine'   => $productLine,
                    'id'            => $productLine->getName(),
                    'categories'    => $this->categoryRepository->findAll(),
                ]);
            }
        }
    }
}