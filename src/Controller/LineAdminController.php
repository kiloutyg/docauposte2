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

class LineAdminController extends BaseController
{

    #[Route('/lineadmin/{id}', name: 'app_line_admin')]

    public function index(AuthenticationUtils $authenticationUtils, string $id = null): Response
    {
        $productline = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productline->getZone();
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('line_admin/line_admin_index.html.twig', [
            'controller_name' => 'LineAdminController',
            'zone'         => $zone,
            'productLines' => $productline,
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/lineadmin/create_manager/{id}', name: 'app_line_admin_create_manager')]


    public function createManager(string $id = null, AccountService $accountService, Request $request): Response
    {
        $productline = $this->productLineRepository->findOneBy(['name' => $id]);
        $zone = $productline->getZone();

        $error = null;
        $result = $accountService->createAccount($request, $error, 'app_productline', [
            'zone'         => $zone,
            'id' => $productline->getName(),
            'productLines' => $productline,
        ]);

        if ($result) {
            $this->addFlash('success', 'Account has been created');
            return $this->redirectToRoute($result['route'], $result['params']);
        }

        if ($error) {
            $this->addFlash('error', $error);
        }

        return $this->redirectToRoute('app_zone', [
            'zone'         => $zone,
            'id' => $zone->getName(),
            'productLines' => $this->productLineRepository->findAll(),
        ]);
    }

    #[Route('/admin/create_productline/{id}', name: 'app_admin_create_productline')]
    public function createProductLine(Request $request, string $id = null)
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $id]);

        // Create a productline
        if ($request->getMethod() == 'POST') {

            $productlinename = $request->request->get('productlinename');

            $zone = $this->zoneRepository->findOneBy(['name' => $id]);

            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);
            if ($productline) {
                $this->addFlash('danger', 'productline already exists');
                return $this->redirectToRoute('app_admin', [
                    'zone'         => $zone,
                    'id' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            } else {
                $productline = new ProductLine();
                $productline->setName($productlinename);
                $productline->setZone($zone);
                $this->em->persist($productline);
                $this->em->flush();
                $this->addFlash('success', 'The Product Line has been created');
                return $this->redirectToRoute('app_admin', [
                    'zone'         => $zone,
                    'id' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            }
        }
    }
}