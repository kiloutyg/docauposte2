<?php

namespace App\Repository;

use App\Entity\Operators;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Operators>
 *
 * @method Operators|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operators|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operators[]    findAll()
 * @method Operators[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperatorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operators::class);
    }

//    /**
//     * @return Operators[] Returns an array of Operators objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Operators
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
