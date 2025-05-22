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

    /**
     * Constructor for the TrainingRecordRepository.
     *
     * Initializes the repository with the TrainingRecord entity class.
     *
     * @param ManagerRegistry $registry The Doctrine registry service used to access entity managers
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingRecord::class);
    }

    /**
     * Compares two operators for sorting purposes based on team, UAP, and name.
     *
     * The comparison follows this hierarchy:
     * 1. First by team name
     * 2. If teams are equal, then by UAP name
     * 3. If UAPs are equal, then by surname
     * 4. If surnames are equal, then by firstname
     *
     * @param Operator $a The first operator to compare
     * @param Operator $b The second operator to compare
     *
     * @return int Returns negative if $a should come before $b,
     *             positive if $a should come after $b,
     *             or zero if they are considered equal
     */
    public function compareOperator(Operator $a, Operator $b): int
    {
        $response = null;
        if ($a->getTeam()->getName() != $b->getTeam()->getName()) {
            $response = strcmp($a->getTeam()->getName(), $b->getTeam()->getName());
        }
        // If 'team' is the same, move on to compare by 'uap'
        if ($a->getUaps()->first()->getName() != $b->getUaps()->first()->getName()) {
            $response = strcmp($a->getUaps()->first()->getName(), $b->getUaps()->first()->getName());
        }

        // If 'uap' is also the same, finally compare by 'surname.firstname'
        list($firstnameA, $surnameA) = explode('.', $a->getName(), 2);
        list($firstnameB, $surnameB) = explode('.', $b->getName(), 2);

        $surnameComparison = strcmp($surnameA, $surnameB);
        if ($surnameComparison !== 0) {
            $response = $surnameComparison;
        }

        return $response ?? strcmp($firstnameA, $firstnameB);
    }
}
