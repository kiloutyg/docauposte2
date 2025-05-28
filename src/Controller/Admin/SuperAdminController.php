<?php

namespace App\Controller\Admin;

use App\Entity\Zone;

use App\Service\Facade\EntityManagerFacade;
use App\Service\Facade\ContentManagerFacade;
use App\Service\ErrorService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


// This controller is responsible for rendering the super admin interface an managing the logic of the super admin interface
class SuperAdminController extends AbstractController
{
    private $entityManagerFacade;
    private $contentManagerFacade;

    public function __construct(
        EntityManagerFacade $entityManagerFacade,
        ContentManagerFacade $contentManagerFacade,
    ) {
        $this->entityManagerFacade = $entityManagerFacade;
        $this->contentManagerFacade = $contentManagerFacade;
    }



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






    // Zone creation logic destined to the super admin, it also creates the folder structure for the zone
    #[Route('/superadmin/create_zone', name: 'app_super_admin_create_zone')]
    public function createZone(Request $request)
    {
        if ($request->getMethod() == 'POST') {
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
    }




    // Zone deletion logic destined to the super admin, it also deletes the folder structure for the zone
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







    // Update method for any stuff necessary during dev
    #[Route('/superadmin/update', name: 'app_super_update')]
    public function updateDB()
    {
        $em = $this->entityManagerFacade->getEntityManager();
        $incidents = $this->entityManagerFacade->getIncidents();
        foreach ($incidents as $incident) {
            $similarNamedincidents = $this->entityManagerFacade->findBy(entityType: 'incident', criteria: ['name' => $incident->getName()]);
            foreach ($similarNamedincidents as $similarNamedincident) {
                if ($incident->getId() != $similarNamedincident->getId()) {
                    $originalName = pathinfo($similarNamedincident->getName(), PATHINFO_FILENAME);
                    $fileExtension = pathinfo($similarNamedincident->getName(), PATHINFO_EXTENSION);
                    $similarNamedincident->setName($originalName . '_' . uniqid('', true) . '.' . $fileExtension);
                    $em->persist($similarNamedincident);
                }
            }
        }

        $em->flush();
        return $this->redirectToRoute('app_base');
    }
}
