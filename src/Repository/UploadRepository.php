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

    public function getNonValidatedUploads()
    {
        $nonValidatedUploads = $this->createQueryBuilder('u')
            ->leftJoin('u.validation', 'v')
            ->where('v.id IS NULL')
            ->getQuery()
            ->getResult();

        return $nonValidatedUploads;
    }

    public function getValidatedUploads()
    {
        $validatedUploads = $this->createQueryBuilder('u')
            ->leftJoin('u.validation', 'v')
            ->where('v.id IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $validatedUploads;
    }

    public function findAllWithAssociations()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.button', 'b')
            ->addSelect('b')
            ->leftJoin('b.Category', 'c')
            ->addSelect('c')
            ->leftJoin('c.ProductLine', 'p')
            ->addSelect('p')
            ->leftJoin('p.zone', 'z')
            ->addSelect('z')
            ->leftJoin('u.validation', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithAssociationsProductLine()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.button', 'b')
            ->addSelect('b')
            ->leftJoin('b.Category', 'c')
            ->addSelect('c')
            ->leftJoin('c.ProductLine', 'p')
            ->addSelect('p')
            ->leftJoin('u.validation', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithAssociationsCategory()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.button', 'b')
            ->addSelect('b')
            ->leftJoin('b.Category', 'c')
            ->addSelect('c')
            ->leftJoin('u.validation', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithAssociationsButton()
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.button', 'b')
            ->addSelect('b')
            ->leftJoin('u.validation', 'v')
            ->addSelect('v')
            ->getQuery()
            ->getResult();
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
