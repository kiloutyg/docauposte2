<?php

namespace App\Repository;

use App\Entity\TrainingRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Operator;
use App\Entity\Upload;

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
        $result = 0;

        // Compare by team name
        if ($a->getTeam()->getName() != $b->getTeam()->getName()) {
            $result = strcmp(string1: $a->getTeam()->getName(), string2: $b->getTeam()->getName());
        }
        // If teams are equal, compare by UAP name
        elseif ($a->getUaps()->first()->getName() != $b->getUaps()->first()->getName()) {
            $result = strcmp(string1: $a->getUaps()->first()->getName(), string2: $b->getUaps()->first()->getName());
        }
        // If UAPs are equal, compare by name (firstname.surname)
        else {
            // Split names to separate first name and last name
            list($firstnameA, $surnameA) = explode(separator: '.', string: $a->getName(), limit: 2);
            list($firstnameB, $surnameB) = explode(separator: '.', string: $b->getName(), limit: 2);

            // Compare surnames
            $surnameComparison = strcmp(string1: $surnameA, string2: $surnameB);
            $result = ($surnameComparison !== 0) ? $surnameComparison : strcmp(string1: $firstnameA, string2: $firstnameB);
        }

        return $result;
    }


}
