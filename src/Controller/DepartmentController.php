<?php

namespace App\Controller;

use App\Service\DepartmentService;
use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/department', name: 'app_department_')]
class DepartmentController extends AbstractController
{
    private $departmentService;
    private $entityDeletionService;
    private $entityFetchingService;
    private $logger;


    public function __construct(
        DepartmentService               $departmentService,
        EntityDeletionService           $entityDeletionService,
        EntityFetchingService           $entityFetchingService,
        LoggerInterface                 $logger,

    ) {
        $this->departmentService        = $departmentService;
        $this->entityDeletionService    = $entityDeletionService;
        $this->entityFetchingService    = $entityFetchingService;
        $this->logger                   = $logger;
    }



    #[Route('/', name: 'view')]
    public function departmentView(): Response
    {
        return $this->render('services/department_services/department_management.html.twig', [
            'departments' => $this->entityFetchingService->getDepartments(),
            'uaps' => $this->entityFetchingService->getUaps(),
            'zones' => $this->entityFetchingService->getZones(),
        ]);
    }


    // Logic to create a new department and display a message
    #[Route('/creation', name: 'creation')]
    public function departmentCreation(Request $request): Response
    {
        try {
            $departmentName = $this->departmentService->departmentCreation($request);
            $this->addFlash('success', 'Le service ' . $departmentName . ' a bien été créé');
            return $this->redirectToRoute('app_department_view');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création du service : ' . $e->getMessage());
            $this->logger->error('Error during department creation', [$e->getMessage()]);
            return $this->redirectToRoute('app_department_view');
        }
    }


    // Logic to modify a new department and display a message
    #[Route('/modification', name: 'modification')]
    public function departmentModification(Request $request): Response
    {
        $this->logger->info('Full request', [$request->request->all()]);
        try {
            $departmentName = $this->departmentService->departmentModification($request);
            $this->addFlash('success', 'Le service ' . $departmentName . ' a bien été modifié');
            return $this->redirectToRoute('app_department_view');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la modification du service : ' . $e->getMessage());
            $this->logger->error('Error during department modification', [$e->getMessage()]);
            return $this->redirectToRoute('app_department_view');
        }
    }


    // Create a route for department deletion. It depends on the entitydeletionService.
    #[Route('/deletion/{departmentId}', name: 'deletion')]
    public function departmentDeletion(int $departmentId): Response
    {
        $entityType = "department";
        if ($this->entityDeletionService->deleteEntity($entityType, $departmentId)) {
            $this->addFlash('success', $entityType . ' has been deleted');
            $this->logger->info('departmentDeletion ' . $entityType . 'has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            $this->logger->error('departmentDeletion ' . $entityType . 'does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }
}
