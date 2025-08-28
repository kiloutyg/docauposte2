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

    public function find(string $entityType, int $id): mixed
    {
        return $this->entityFetchingService->find($entityType, $id);
    }

    public function findBy(string $entityType, array $criteria): mixed
    {
        return $this->entityFetchingService->findBy(entityType: $entityType, criteria: $criteria);
    }
    public function findOneBy(string $entityType, array $criteria): mixed
    {
        return $this->entityFetchingService->findOneBy($entityType, $criteria);
    }

    public function count(string $entityType, array $criteria): mixed
    {
        return $this->entityFetchingService->count($entityType, $criteria);
    }

    public function deleteEntity(string $entityType, int $id): bool|string
    {
        return $this->entityDeletionService->deleteEntity($entityType, $id);
    }

    public function deleteFile(int $id)
    {
        return $this->entityDeletionService->deleteFile($id);
    }

    public function uploadsByParentEntity(string $entityType, ?object $entity = null): array
    {
        return $this->entityHeritanceService->uploadsByParentEntity($entityType, $entity);
    }

    public function incidentsByParentEntity(string $entityType, ?object $entity = null): array
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

    public function findDeactivatedOperators()
    {
        return $this->entityFetchingService->findDeactivatedOperators();
    }

    public function findOperatorWithNoRecentTraining()
    {
        return $this->entityFetchingService->findOperatorWithNoRecentTraining();
    }

    public function findInActiveOperators()
    {
        return $this->entityFetchingService->findInActiveOperators();
    }

    public function findOperatorToBeDeleted()
    {
        return $this->entityFetchingService->findOperatorToBeDeleted();
    }

    public function findOperatorToBeDeletedWithNoDelayRestriction()
    {
        return $this->entityFetchingService->findOperatorToBeDeletedWithNoDelayRestriction();
    }

    public function findBySearchQuery(string $name, string $code, string $team, string $uap, string $trainer)
    {
        return $this->entityFetchingService->findBySearchQuery($name, $code, $team, $uap, $trainer);
    }

    public function findIluoBySearchQuery(?string $name = null, ?string $code = null, ?string $team = null, ?string $uap = null)
    {
        return $this->entityFetchingService->findIluoBySearchQuery(name: $name, code: $code, team: $team, uap: $uap);
    }

    public function getTeams()
    {
        return $this->entityFetchingService->getTeams();
    }

    public function getUaps()
    {
        return $this->entityFetchingService->getUaps();
    }
}
