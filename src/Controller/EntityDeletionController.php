<?php

namespace App\Controller;

use App\Service\EntityDeletionService;
use App\Service\Factory\RepositoryFactory;

use App\Form\EntityDeletionType;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class EntityDeletionController extends AbstractController
{
    private $logger;
    private $entityDeletionService;
    private $repositoryFactory;

    public function __construct(
        LoggerInterface                 $logger,
        EntityDeletionService           $entityDeletionService,
        RepositoryFactory               $repositoryFactory
    ) {
        $this->logger                   = $logger;
        $this->entityDeletionService    = $entityDeletionService;
        $this->repositoryFactory        = $repositoryFactory;
    }

    /**
     * Handles the deletion of an entity based on the submitted form data.
     *
     * This method processes a POST request containing entity deletion information,
     * validates the form submission, and attempts to delete the specified entity
     * using the EntityDeletionService. It logs the process steps and provides
     * user feedback through flash messages.
     *
     * @param Request $request The HTTP request containing the entity deletion form data
     *                         with 'entityType', 'entityId', and 'originPath' parameters
     *
     * @return Response A redirect response either to the specified origin path
     *                  or back to the referring page
     */
    #[Route('delete/entity', name: 'delete_entity', methods: ['POST'])]
    public function deleteEntity(Request $request): Response
    {

        $this->logger->info('Full request', [
            'request' => $request->request->all()
        ]);

        $delete_entity = $request->request->all('delete_entity');
        $this->logger->info('Delete entity : ', [$delete_entity]);
        $entityType = $delete_entity['entityType'];
        $this->logger->info('Entity type : ', [$entityType]);
        $entityId = (int)$delete_entity['entityId'];
        $this->logger->info('Entity id : ', [$entityId]);
        $originPath = $delete_entity['originPath'];
        $this->logger->info('Origin path : ', [$originPath]);

        if ($originPath) {
            $return = $this->redirectToRoute($originPath);
        } else {
            $return = $this->redirect($request->headers->get('referer'));
        }


        $form = $this->createForm(EntityDeletionType::class, null, [
            'entityType' => $entityType,
            'entityId' => $entityId,
            'originPath' => $originPath,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->entityDeletionService->deleteEntity($entityType, $entityId);
            } catch (\Exception $e) {
                $this->logger->error('Error while deleting entity ', [$e->getMessage()]);
                $this->addFlash('error', 'Erreur lors de la suppression de l\'entité : ' . $e->getMessage());
                return $this->redirectToRoute($originPath);
            }

            $this->logger->info('Entity deletion confirm', [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'originPath' => $originPath,
            ]);

            $this->addFlash('success', 'L\'entité a bien été supprimée.');
            $this->logger->info('Entity deletion success');
        } else {
            $this->addFlash('error', 'Formulaire invalide');
            $this->logger->error('Invalid form', [$form->getErrors()]);
        }
        return $return;
    }





    
    #[Route(path: '/superadmin/delete/trduplicate', name: 'app_delete_tr_duplicate')]
    public function deleteTRDuplicate(): Response
    {
        $this->logger->info('Delete TR duplicate');

        // Get all training records
        $allTrainingRecords = $this->repositoryFactory->getRepository('trainingRecord')->findAll();
        $this->logger->info('All training records count', [count($allTrainingRecords)]);

        // Group records by operator and upload to find duplicates
        $groupedRecords = [];
        $duplicates = [];

        foreach ($allTrainingRecords as $record) {
            $operatorId = $record->getOperator()->getId();
            $uploadId = $record->getUpload()->getId();
            $key = $operatorId . '-' . $uploadId;

            if (!isset($groupedRecords[$key])) {
                $groupedRecords[$key] = $record;
            } else {
                // We found a duplicate
                $duplicates[] = $record;
            }
        }

        $this->logger->info('Found duplicate records', [$duplicates]);

        // Delete the duplicates
        if (!empty($duplicates)) {

            foreach ($duplicates as $duplicate) {
                $this->logger->info('Deleting duplicate record', [
                    'id' => $duplicate->getId(),
                    'operator' => $duplicate->getOperator()->getName(),
                    'upload' => $duplicate->getUpload()->getId()
                ]);

                $this->entityDeletionService->deleteEntity('trainingRecord', $duplicate->getId());
            }

            $this->addFlash('success', count($duplicates) . ' duplicate training records have been deleted.');
        } else {
            $this->addFlash('info', 'No duplicate training records were found.');
        }

        return $this->redirectToRoute('app_base');
    }
}
