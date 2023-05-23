<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


use App\Service\AccountService;
use App\Entity\ProductLine;

class ZoneAdminController extends BaseController
{


    #[Route('/zone_admin/{zone}', name: 'app_zone_admin')]

    public function index(AuthenticationUtils $authenticationUtils, string $zone = null): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);
        // Get the error and last username using AuthenticationUtils
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('zone_admin/zone_admin_index.html.twig', [
            'zone'         => $zone,
            'productLines' => $this->productLineRepository->findAll(),
            'error' => $error,
            'last_username' => $lastUsername,
            'buttons'     => $this->buttonRepository->findAll(),
            'uploads'     => $this->uploadRepository->findAll(),
            'users' => $this->userRepository->findAll()

        ]);
    }


    #[Route('/zone_admin/create_line_admin/{zone}', name: 'app_zone_admin_create_line_admin')]

    public function createLineAdmin(string $zone = null, AccountService $accountService, Request $request): Response
    {
        $zone = $this->zoneRepository->findOneBy(['name' => $zone]);

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

        return $this->redirectToRoute('app_zone', [
            'zone'         => $zone,
            'id' => $zone->getName(),
            'productLines' => $this->productLineRepository->findAll(),
        ]);
    }


    #[Route('/zone_admin/create_productline/{zone}', name: 'app_zone_admin_create_productline')]
    public function createProductLine(Request $request, string $zone = null)
    {
        // 
        $zone = $this->zoneRepository->findOneBy(['id' => $zone]);

        if (!preg_match("/^[^.]+$/", $request->request->get('productlinename'))) {
            // Handle the case when productlinne name contains disallowed characters
            $this->addFlash('danger', 'Nom de ligne de produit invalide');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone->getName(),
                'productLines' => $this->productLineRepository->findAll(),
            ]);
        } else {
            // Create a productline

            $productlinename = $request->request->get('productlinename') . '.' . $zone->getName();
            $productline = $this->productLineRepository->findOneBy(['name' => $productlinename]);

            if ($productline) {
                $this->addFlash('danger', 'productline already exists');
                return $this->redirectToRoute('app_zone_admin', [
                    'zone' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            } else {
                $productline = new ProductLine();
                $productline->setName($productlinename);
                $productline->setZone($zone);
                $this->em->persist($productline);
                $this->em->flush();

                $this->addFlash('success', 'The Product Line has been created');
                return $this->redirectToRoute('app_zone_admin', [
                    'zone' => $zone->getName(),
                    'productLines' => $this->productLineRepository->findAll(),
                ]);
            }
        }
    }

    #[Route('/zone_admin/delete_productline/{productline}', name: 'app_zone_admin_delete_productline')]
    public function deleteEntity(string $productline): Response
    {
        $entityType = 'productline';
        $entityid = $this->productLineRepository->findOneBy(['name' => $productline]);

        $entity = $this->entitydeletionService->deleteEntity($entityType, $entityid->getId());

        $zone = $entityid->getZone()->getName();

        if ($entity == true) {

            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone,
            ]);
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_zone_admin', [
                'zone' => $zone,
            ]);
        }
    }
}