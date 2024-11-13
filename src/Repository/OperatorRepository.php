<?php

namespace App\Repository;

use App\Entity\Operator;

use App\Repository\SettingsRepository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

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
    private $logger;
    private $em;
    private $settingsRepository;

    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SettingsRepository $settingsRepository,
    ) {
        parent::__construct($registry, Operator::class);
        $this->logger = $logger;
        $this->em = $em;
        $this->settingsRepository = $settingsRepository;
            }


    public function findAllOrdered()
    {
        $this->logger->info('Finding all operators ordered.');

        $operators = $this->findOperatorsSortedByLastNameFirstName();

        usort($operators, function ($a, $b) {
            // Compare by 'team'
            if ($a->getTeam()->getId() != $b->getTeam()->getId()) {
                return strcmp($a->getTeam()->getName(), $b->getTeam()->getName());
            }
            // If 'team' is the same, move on to compare by 'uap'
            if ($a->getUap()->getId() != $b->getUap()->getId()) {
                return strcmp($a->getUap()->getName(), $b->getUap()->getName());
            }
        });

        return $operators;
    }



    public function findOperatorsSortedByLastNameFirstName()
    {
        $this->logger->info('Finding operators sorted last name, and first name.');


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
            if ($a->getTeam() !== null && $b->getTeam() !== null && $teamComparison = strcmp($a->getTeam()->getName(), $b->getTeam()->getName())) {
                return $teamComparison;
            }
            // If team name is the same, compare by UAP name
            if ($a->getUap() !== null && $b->getUap() !== null && $uapComparison = strcmp($a->getUap()->getName(), $b->getUap()->getName())) {
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



    public function findBySearchQuery($name, $code, $team, $uap, $trainer)
    {

        $this->logger->info('Finding operators by search query.');

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.team', 't')
            ->leftJoin('o.uap', 'u');

        if (!empty($name)) {
            $qb->andWhere('LOWER(o.name) LIKE :name')
                ->setParameter('name', '%' . strtolower($name) . '%');
        }
        if (!empty($code)) {
            $qb->andWhere('o.code LIKE :code')
                ->setParameter('code', '%' . $code . '%');
        }
        if (!empty($team)) {
            $qb->andWhere('LOWER(t.name) LIKE :team')
                ->setParameter('team', '%' . strtolower($team) . '%');
        }
        if (!empty($uap)) {
            $qb->andWhere('LOWER(u.name) LIKE :uap')
                ->setParameter('uap', '%' . strtolower($uap) . '%');
        }

        // $this->logger->info('Trainer value in repository methods: ' . $trainer);
        // Handling trainer value based on true, false, or null
        switch ($trainer) {
            case "true":
                $trainer = true;
                break;
            case "false":
                $trainer = false;
                break;
            case "all":
                $trainer = null;
                break;
        }

        // $this->logger->info('Trainer value after handling: ' . $trainer);

        if ($trainer === true) {
            // $this->logger->info('Trainer value true, select those who are trainer.');
            $qb->setParameter('trainerStatus', true)
                ->andWhere('o.IsTrainer = :trainerStatus');
        } elseif ($trainer === false) {
            // $this->logger->info('Trainer value false, select those who are not trainers or undefined.');
            $qb->setParameter('trainerStatus', false)
                ->andWhere('o.IsTrainer = :trainerStatus OR o.IsTrainer IS NULL');
        } elseif ($trainer === null) {
            // If $trainer is null, and you want to select all without any filter on IsTrainer, do not add any where clause related to IsTrainer.
            // $this->logger->info('Trainer value is null, no filter applied on trainer status.');
            // No further action needed if you want all records regardless of trainer status.
        }

        $result = $this->orderOperator($qb->getQuery()->getResult());
        return $result;
    }


    public function orderOperator($operators)
    {

        $this->logger->info('Ordering operators by team, UAP, last name, and first name.');

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
            if ($a->getTeam() !== null && $b->getTeam() !== null && $teamComparison = strcmp($a->getTeam()->getName(), $b->getTeam()->getName())) {
                return $teamComparison;
            }
            // If team name is the same, compare by UAP name
            if ($a->getUap() !== null && $b->getUap() !== null && $uapComparison = strcmp($a->getUap()->getName(), $b->getUap()->getName())) {
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



    public function findByNameLikeForSuggestions(string $name): array
    {
        $this->logger->info('Finding operators by name for suggestions.');

        if (!preg_match('/^[a-z]+(-[a-z]+)*$/i', $name)) {
            throw new \InvalidArgumentException("Invalid name format.");
        }

        $name = strtolower($name); // Normalize input to lower case
        $searchPattern = '%' . $name . '%'; // Create a search pattern for LIKE

        // Adjusted query for newer DBAL versions
        $sql = "SELECT * FROM operator WHERE SOUNDEX(name) = SOUNDEX(:name) OR name LIKE :pattern";

        // Use executeQuery for select statements
        try {
            $conn = $this->em->getConnection();
            $stmt = $conn->executeQuery($sql, [
                'name' => $name,
                'pattern' => $searchPattern
            ]);

            // Use fetchAllAssociative to fetch data
            return $stmt->fetchAllAssociative();
        } catch (\Doctrine\DBAL\Exception $exception) {

            // Log the error and possibly rethrow or handle gracefully
            $this->logger->error('Database query error: ' . $exception->getMessage());
            throw new \RuntimeException("Database query failed", 0, $exception);
        }
    }

    // Used in the methods that add the tobedeleted datetime value in the appropriate field to the operator entity
    public function findOperatorWithNoRecentTraining()
    {
        $this->logger->info('Finding operators with no recent training.');
        # Related to Settings -> OperatorRetrainingDelay
        $retrainingDelay = new \DateTime();
        $retrainingDelay->modify('-6 months');

        $operators = $this->createQueryBuilder('o')
            ->where('o.lasttraining < :retrainingDelay')
            ->setParameter('retrainingDelay', $retrainingDelay)
            ->andWhere('o.tobedeleted IS NULL')
            ->getQuery()
            ->getResult();

        $this->logger->info('operators to be retrained: ', [$operators]);
        return $operators;
    }

    // Used in the methods that check for operators to be deleted, count them, display them in appropriate views etc
    public function findInActiveOperators()
    {
        $this->logger->info('Finding operators with no recent training.');
        # Related to Settings -> Lack thereof an appropriate setting
        $inactiveDelay = new \DateTime();
        $inactiveDelay->modify('-3 months');

        $operators = $this->createQueryBuilder('o')
            ->where('o.lasttraining < :inactiveDelay')
            ->setParameter('inactiveDelay', $inactiveDelay)
            ->andWhere('o.tobedeleted IS NOT NULL')
            ->getQuery()
            ->getResult();

        $this->logger->info('inactive operators: ', [$operators]);
        return $operators;
    }

    // Used in the methods that delete the operator entity
    public function findOperatorToBeDeleted()
    {
        $this->logger->info('Finding operators to be deleted.');
        # Related to Settings -> AutoDeleteOperatorDelay
        $AutoDeleteDelay = new \DateTime();
        $AutoDeleteDelay->modify('-3 months');

        $operatorIds = $this->createQueryBuilder('o')
            ->select('o.id')
            ->where('o.tobedeleted < :AutoDeleteDelay')
            ->setParameter('AutoDeleteDelay', $AutoDeleteDelay)
            ->getQuery()
            ->getScalarResult();

        $this->logger->info('To be deleted operators: ' . json_encode($operatorIds));
        // Extract IDs from the result
        return array_column($operatorIds, 'id');
    }
    // public function findBySearchQuery($search)
    // {
    //     return $this->createQueryBuilder('o')
    //         ->andWhere('LOWER(o.name) LIKE :search OR LOWER(t.name) LIKE :search OR LOWER(u.name) LIKE :search')
    //         ->leftJoin('o.team', 't')
    //         ->leftJoin('o.uap', 'u')
    //         ->setParameter('search', '%' . strtolower($search) . '%')
    //         ->getQuery()
    //         ->getResult();
    // }

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
