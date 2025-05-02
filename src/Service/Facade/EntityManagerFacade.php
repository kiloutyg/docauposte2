<?php

namespace App\Service\Facade;

use App\Service\EntityDeletionService;
use App\Service\EntityFetchingService;
use App\Service\EntityHeritanceService;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerFacade
{
    private $em;
    private $entityDeletionService;
    private $entityFetchingService;
    private $entityHeritanceService;

    public function __construct(
        EntityManagerInterface $em,
        EntityDeletionService $entityDeletionService,
        EntityFetchingService $entityFetchingService,
        EntityHeritanceService $entityHeritanceService
    ) {
        $this->em = $em;
        $this->entityDeletionService = $entityDeletionService;
        $this->entityFetchingService = $entityFetchingService;
        $this->entityHeritanceService = $entityHeritanceService;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function find(string $entityType, int $id)
    {
        return $this->entityFetchingService->find($entityType, $id);
    }

    public function findBy(string $entityType, array $criteria)
    {
        return $this->entityFetchingService->findBy($entityType, $criteria);
    }
    public function findOneBy(string $entityType, array $criteria)
    {
        return $this->entityFetchingService->findOneBy($entityType, $criteria);
    }

    public function count(string $entityType, array $criteria)
    {
        return $this->entityFetchingService->count($entityType, $criteria);
    }

    public function deleteEntity(string $entityType, int $id)
    {
        return $this->entityDeletionService->deleteEntity($entityType, $id);
    }

    public function uploadsByParentEntity(string $entityType, $entity)
    {
        return $this->entityHeritanceService->uploadsByParentEntity($entityType, $entity);
    }

    public function incidentsByParentEntity(string $entityType, $entity)
    {
        return $this->entityHeritanceService->incidentsByParentEntity($entityType, $entity);
    }

    public function getIncidentCategories()
    {
        return $this->entityFetchingService->getIncidentCategories();
    }

    public function getZones()
    {
        return $this->entityFetchingService->getZones();
    }

    public function getAllUploadsWithAssociations()
    {
        return $this->entityFetchingService->getAllUploadsWithAssociations();
    }

    public function getIncidents()
    {
        return $this->entityFetchingService->getIncidents();
    }
}
