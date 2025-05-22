<?php

namespace App\Repository;

use App\Entity\ProductLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductLine>
 *
 * @method ProductLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductLine[]    findAll()
 * @method ProductLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductLineRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductLine::class);
    }

    public function save(ProductLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
