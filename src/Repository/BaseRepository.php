<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class BaseRepository extends ServiceEntityRepository
{

    /**
     * Retrieves all entities except the one with the specified ID.
     *
     * @param mixed $entityId The ID of the entity to exclude from the results
     * @return array An array of entities excluding the one with the specified ID
     */
    public function findAllExceptOne($entityId)
    {
        return $this->createQueryBuilder('e')
            ->where('e.id != :entityId')
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getResult();
    }


    /**
     * Retrieves all entities related to a specific parent entity, excluding one entity by its ID.
     *
     * @param mixed $entityId The ID of the entity to exclude from the results
     * @param mixed $parentEntityId The ID of the parent entity to filter by
     * @param string $parentFieldName The name of the field that represents the relationship to the parent entity
     * @return array An array of entities that belong to the specified parent and exclude the one with the specified ID
     */
    public function findAllExceptOneByParent($entityId, $parentEntityId, $parentFieldName)
    {
        return $this->createQueryBuilder('e')
            ->join('e.' . $parentFieldName, 'p')
            ->where('e.id != :entityId')
            ->andWhere('p.id = :parentEntityId')
            ->setParameter('entityId', $entityId)
            ->setParameter('parentEntityId', $parentEntityId,)
            ->getQuery()
            ->getResult();
    }
}
