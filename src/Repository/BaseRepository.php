<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class BaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function findAllExceptOne($entityId)
    {
        return $this->createQueryBuilder('e')
            ->where('e.id != :entityId')
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getResult();
    }


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
