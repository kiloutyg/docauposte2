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
}