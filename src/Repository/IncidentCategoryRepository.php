<?php

namespace App\Repository;

use App\Entity\IncidentCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IncidentCategory>
 *
 * @method IncidentCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncidentCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncidentCategory[]    findAll()
 * @method IncidentCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidentCategoryRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncidentCategory::class);
    }

    public function save(IncidentCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IncidentCategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
