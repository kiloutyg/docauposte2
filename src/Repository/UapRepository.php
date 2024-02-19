<?php

namespace App\Repository;

use App\Entity\Uap;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Uap>
 *
 * @method Uap|null find($id, $lockMode = null, $lockVersion = null)
 * @method Uap|null findOneBy(array $criteria, array $orderBy = null)
 * @method Uap[]    findAll()
 * @method Uap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UapRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Uap::class);
    }

//    /**
//     * @return Uap[] Returns an array of Uap objects
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

//    public function findOneBySomeField($value): ?Uap
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
