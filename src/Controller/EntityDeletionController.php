<?php

namespace App\Controller;

use App\Service\EntityDeletionService;

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

    public function __construct(
        LoggerInterface                 $logger,
        EntityDeletionService           $entityDeletionService
    ) {
        $this->logger                   = $logger;
        $this->entityDeletionService    = $entityDeletionService;
    }

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
}
