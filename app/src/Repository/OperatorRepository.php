<?php

namespace App\Repository;

use App\Entity\Operator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Operator>
 *
 * @method Operator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operator[]    findAll()
 * @method Operator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operator::class);
    }

    public function findAllOrdered()
    {
        $operators = $this->findAll();

        usort($operators, function ($a, $b) {
            // Compare by 'team'
            if ($a->getTeam()->getId() != $b->getTeam()->getId()) {
                return strcmp($a->getTeam()->getName(), $b->getTeam()->getName());
            }
            // If 'team' is the same, move on to compare by 'uap'
            if ($a->getUap()->getId() != $b->getUap()->getId()) {
                return strcmp($a->getUap()->getName(), $b->getUap()->getName());
            }
            // If 'uap' is also the same, finally compare by 'name'
            return strcmp($a->getName(), $b->getName());
        });

        return $operators;
    }



    //    /**
    //     * @return Operator[] Returns an array of Operator objects
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

    //    public function findOneBySomeField($value): ?Operator
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
