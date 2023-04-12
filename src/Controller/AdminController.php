<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;

use App\Controller\SecurityController;
use App\Service\AccountService;
use App\Repository\ProductLineRepository;
use App\Entity\ProductLine;

class AdminController extends BaseController
{

    #[Route('/admin/{id}', name: 'app_admin')]

    public function index(AuthenticationUtils $authenticationUtils, string $id = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $id]);
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/admin_index.html.twig', [
            'controller_name' => 'AdminController',
            'zone'         => $zone,
            'productLines' => $this->productLineRepository->findAll(),
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/admin/create_manager', name: 'app_admin_create_manager')]
    public function createAdmin(AccountService $accountService, Request $request): Response
    {

        // Use createAccount() function from AccountService

        $user = $accountService->createAccount($request, $error);

        if ($user) {
            // Handle the created user, for example, by redirecting to a specific route
            // return $this->redirectToRoute('some_route');

            $this->addFlash('success', 'account has been created');
            return $this->redirectToRoute('app_admin');
        }
        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/create_productline', name: 'app_admin_create_productline')]
    public function createProductLine(Request $request)
    {
        // Create a productline
        if ($request->getMethod() == 'POST') {
            $productlinename = $request->request->get('productlinename');


            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);
            if ($productline) {
                $this->addFlash('danger', 'productline already exists');
                return $this->redirectToRoute('app_admin');
            } else {
                $productline = new ProductLine();
                $productline->setName($productlinename);
                $this->em->persist($productline);
                $this->em->flush();
                $this->addFlash('success', 'The Product Line has been created');
                return $this->redirectToRoute('app_admin');
            }
        }
    }
}