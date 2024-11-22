<?php

namespace App\Repository;

use App\Entity\Upload;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Upload>
 *
 * @method Upload|null find($id, $lockMode = null, $lockVersion = null)
 * @method Upload|null findOneBy(array $criteria, array $orderBy = null)
 * @method Upload[]    findAll()
 * @method Upload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Upload::class);
    }

    public function save(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get uploads with optional associations and filters.
     *
     * @param array $associations
     * @param bool  $validatedOnly
     * @return Upload[]
     */
    private function getUploads(array $associations = [], bool $validatedOnly = false)
    {
        $qb = $this->createQueryBuilder('u');

        // Join associations based on input array
        if (in_array('button', $associations)) {
            $qb->leftJoin('u.button', 'b')
                ->addSelect('b');
        }

        if (in_array('category', $associations)) {
            $qb->leftJoin('b.Category', 'c')
                ->addSelect('c');
        }

        if (in_array('productLine', $associations)) {
            $qb->leftJoin('c.ProductLine', 'p')
                ->addSelect('p');
        }

        if (in_array('zone', $associations)) {
            $qb->leftJoin('p.zone', 'z')
                ->addSelect('z');
        }

        if (in_array('validation', $associations)) {
            $qb->leftJoin('u.validation', 'v')
                ->addSelect('v');
        }

        // Apply validation filter if needed
        if ($validatedOnly) {
            $qb->where('v.id IS NOT NULL')
                ->andWhere('v.Status = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllValidatedUploadsWithAssociations()
    {
        return $this->getUploads(
            ['button', 'category', 'productLine', 'zone', 'validation'],
            true
        );
    }

    public function findAllWithAssociations()
    {
        return $this->getUploads(
            ['button', 'category', 'productLine', 'zone', 'validation']
        );
    }

    public function findAllWithAssociationsProductLine()
    {
        return $this->getUploads(
            ['button', 'category', 'productLine', 'validation']
        );
    }

    public function findAllWithAssociationsCategory()
    {
        return $this->getUploads(
            ['button', 'category', 'validation']
        );
    }

    public function findAllWithAssociationsButton()
    {
        return $this->getUploads(
            ['button', 'validation']
        );
    }


    //    /**
    //     * @return Upload[] Returns an array of Upload objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Upload
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
