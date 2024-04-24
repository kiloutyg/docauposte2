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



    public function findOperatorsSortedByLastNameFirstName()
    {
        // Fetch all operators with their team and UAP
        $operators = $this->createQueryBuilder('o')
            ->join('o.team', 't')
            ->join('o.uap', 'u')
            ->select('o, t, u')
            ->getQuery()
            ->getResult();

        // Sort the operators in PHP
        usort($operators, function ($a, $b) {
            // Split names to separate first name and last name
            list($firstNameA, $lastNameA) = explode('.', $a->getName());
            list($firstNameB, $lastNameB) = explode('.', $b->getName());

            // Normalize for case insensitive comparison
            $lastNameA = strtolower($lastNameA);
            $lastNameB = strtolower($lastNameB);
            $firstNameA = strtolower($firstNameA);
            $firstNameB = strtolower($firstNameB);

            // Compare by team name
            if ($teamComparison = strcmp($a->getTeam()->getName(), $b->getTeam()->getName())) {
                return $teamComparison;
            }
            // If team name is the same, compare by UAP name
            if ($uapComparison = strcmp($a->getUap()->getName(), $b->getUap()->getName())) {
                return $uapComparison;
            }
            // If UAP name is the same, compare by last name
            if ($lastNameComparison = strcmp($lastNameA, $lastNameB)) {
                return $lastNameComparison;
            }
            // If last name is the same, compare by first name
            return strcmp($firstNameA, $firstNameB);
        });

        return $operators;
    }

    public function findBySearchQuery($search)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.lastName LIKE :search OR o.firstName LIKE :search OR t.name LIKE :search OR u.name LIKE :search')
            ->leftJoin('o.team', 't')
            ->leftJoin('o.uap', 'u')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getResult();
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
