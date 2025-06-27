<?php

namespace App\Controller\Admin;

use App\Entity\Zone;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;
use App\Service\Operator\TrainingRecordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


// This controller is responsible for rendering the super admin interface an managing the logic of the super admin interface
class SuperAdminController extends AbstractController
{
    private $entityManagerFacade;
    private $contentManagerFacade;
    private $trainingRecordService;

    public function __construct(
        EntityManagerFacade $entityManagerFacade,
        ContentManagerFacade $contentManagerFacade,
        TrainingRecordService $trainingRecordService
    ) {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->contentManagerFacade = $contentManagerFacade;
        $this->trainingRecordService = $trainingRecordService;
    }



    /**
     * Renders the super admin dashboard interface.
     *
     * This method fetches all necessary data for the super admin dashboard including uploads,
     * incidents, and zones. It processes the data through various services to group and organize
     * the information before rendering the admin template.
     *
     * @return Response A Symfony Response object containing the rendered admin dashboard template
     *                  with all required data for display
     */
    #[Route('/superadmin', name: 'app_super_admin')]
    public function superAdmin(): Response
    {
        $pageLevel = 'super';

        $uploads = $this->entityManagerFacade->getAllUploadsWithAssociations();
        $incidents = $this->entityManagerFacade->getIncidents();

        $uploadsArray = $this->contentManagerFacade->groupAllUploads($uploads);
        $groupedUploads = $uploadsArray[0];
        $groupedValidatedUploads = $uploadsArray[1];
        $groupIncidents = $this->contentManagerFacade->groupIncidents($incidents);

        return $this->render('admin_template/admin_index.html.twig', [
            'pageLevel'                 => $pageLevel,
            'groupedUploads'            => $groupedUploads,
            'groupedValidatedUploads'   => $groupedValidatedUploads,
            'groupincidents' => $groupIncidents,
            'zones'                     => $this->entityManagerFacade->getZones(),
        ]);
    }







    /**
     * Creates a new zone in the system.
     *
     * This method handles the creation of a new zone based on the submitted form data.
     * It validates that the zone name is not empty and doesn't already exist in the system.
     * If validation passes, it creates the zone entity, persists it to the database,
     * and creates the necessary folder structure for the zone.
     *
     * @param Request $request The HTTP request containing the zone name in the POST data
     *
     * @return Response A Symfony Response object that redirects to the super admin dashboard
     *                  with appropriate flash messages indicating success or failure
     */
    #[Route('/superadmin/create_zone', name: 'app_super_admin_create_zone', methods: ['POST'])]
    public function createZone(Request $request)
    {
        $zonename = trim($request->request->get('zonename'));
        $zone = $this->entityManagerFacade->findOneBy('zone', ['name' => $zonename]);
        if (empty($zonename)) {
            $this->addFlash('danger', 'Le nom de la Zone ne peut être vide');
            return $this->redirectToRoute('app_super_admin');
        }

        if ($zone) {
            $this->addFlash('danger', 'La zone existe déjà');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $count = $this->entityManagerFacade->count('zone', []);
            $sortOrder = $count + 1;
            $zone = new Zone();
            $zone->setName($zonename);
            $zone->setSortOrder($sortOrder);
            $zone->setCreator($this->getUser());

            $em = $this->entityManagerFacade->getEntityManager();
            $em->persist($zone);
            $em->flush();
            $this->contentManagerFacade->folderStructure($zonename);

            $this->addFlash('success', 'La zone a été créée');
            return $this->redirectToRoute('app_super_admin');
        }
    }




    // Zone deletion logic destined to the super admin, it also deletes the folder structure for the zone
    /**
     * Deletes a zone entity from the system.
     *
     * This method handles the deletion of a zone entity based on its ID. It also
     * displays appropriate flash messages to inform the user about the result of the operation.
     *
     * @param int $zoneId The unique identifier of the zone to be deleted
     *
     * @return Response A Symfony Response object that redirects to the super admin dashboard
     */
    #[Route('/superadmin/delete_zone/{zoneId}', name: 'app_super_admin_delete_zone')]
    public function deleteEntityZone(int $zoneId): Response
    {
        $entityType = 'zone';

        $response = $this->entityManagerFacade->deleteEntity($entityType, $zoneId);

        if ($response) {
            $this->addFlash('success', $entityType . ' has been deleted');
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger',  $entityType . '  does not exist');
            return $this->redirectToRoute('app_super_admin');
        }
    }

    // A route that use a method to revalid automatically every training records of a certain date
    #[Route('superadmin/cheattrain/{uploadId}', name: 'app_super_admin_cheat_train')]
    public function cheatTrain(int $uploadId)
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->trainingRecordService->cheatTrain($uploadId);
            return $this->redirectToRoute('app_super_admin');
        } else {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à accéder à cette page');
            return $this->redirectToRoute('app_login');
        }
    }
}
