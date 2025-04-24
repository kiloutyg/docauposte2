<?php

namespace App\Repository;

use App\Entity\Validation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Validation>
 *
 * @method Validation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Validation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Validation[]    findAll()
 * @method Validation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Validation::class);
    }


    public function findNonValidatedValidations()
    {
        $nonValidatedValidations = $this->createQueryBuilder('v')
            ->where('v.status != true OR v.status IS NULL')
            ->getQuery()
            ->getResult();

        return $nonValidatedValidations;
    }

    //    /**
    //     * @return Validation[] Returns an array of Validation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Validation
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
