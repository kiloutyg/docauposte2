<?php

namespace App\Repository;

use App\Entity\Iluo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Iluo>
 */
class IluoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Iluo::class);
    }


    public function findBySearchQuery(?string $name = null, ?string $code = null, ?string $team = null, ?string $uap = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.team', 't')
            ->leftJoin('o.uaps', 'u');

        if (!empty($name)) {
            $qb->andWhere('LOWER(o.name) LIKE :name')
                ->setParameter('name', '%' . strtolower($name) . '%');
        }
        if (!empty($code)) {
            $qb->andWhere('o.code LIKE :code')
                ->setParameter('code', '%' . $code . '%');
        }
        if (!empty($team)) {
            $qb->andWhere('LOWER(t.name) LIKE :team')
                ->setParameter('team', '%' . strtolower($team) . '%');
        }
        if (!empty($uap)) {
            $qb->andWhere('LOWER(u.name) LIKE :uap')
                ->setParameter('uap', '%' . strtolower($uap) . '%');
        }

        // Handling trainer value based on true, false, or null

        return $this->operatorComparison($qb->getQuery()->getResult());
    }
}
