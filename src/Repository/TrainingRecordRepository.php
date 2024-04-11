<?php

namespace App\Repository;

use App\Entity\TrainingRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Operator;

/**
 * @extends ServiceEntityRepository<TrainingRecord>
 *
 * @method TrainingRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingRecord[]    findAll()
 * @method TrainingRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingRecord::class);
    }

    public function compareOperator(Operator $a, Operator $b): int
    {
        if ($a->getTeam()->getName() != $b->getTeam()->getName()) {
            return strcmp($a->getTeam()->getName(), $b->getTeam()->getName());
        }
        // If 'team' is the same, move on to compare by 'uap'
        if ($a->getUap()->getName() != $b->getUap()->getName()) {
            return strcmp($a->getUap()->getName(), $b->getUap()->getName());
        }

        // If 'uap' is also the same, finally compare by 'surname.firstname'
        list($firstnameA, $surnameA) = explode('.', $a->getName(), 2);
        list($firstnameB, $surnameB) = explode('.', $b->getName(), 2);

        $surnameComparison = strcmp($surnameA, $surnameB);
        if ($surnameComparison !== 0) {
            return $surnameComparison;
        }

        return strcmp($firstnameA, $firstnameB);
    }

    //    /**
    //     * @return TrainingRecord[] Returns an array of TrainingRecord objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TrainingRecord
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
