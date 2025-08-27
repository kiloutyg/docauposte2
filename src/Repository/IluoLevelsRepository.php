<?php

namespace App\Repository;

use App\Entity\IluoLevels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IluoLevels>
 */
class IluoLevelsRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IluoLevels::class);
    }

    public function priorityOrderExists(int $priorityOrder): bool
    {
        $queryBuilder = $this->createQueryBuilder('il');
        $queryBuilder->select('COUNT(il.id)')
            ->where('il.priorityOrder = :priorityOrder')
            ->setParameter('priorityOrder', $priorityOrder);

        return (bool) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
