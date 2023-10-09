<?php

namespace App\Repository;

use App\Entity\Approbation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Approbation>
 *
 * @method Approbation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approbation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approbation[]    findAll()
 * @method Approbation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprobationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approbation::class);
    }

//    /**
//     * @return Approbation[] Returns an array of Approbation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Approbation
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
