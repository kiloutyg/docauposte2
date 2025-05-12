<?php

namespace App\Repository;

use App\Entity\Operator;

use App\Service\SettingsService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Operator>
 */

class OperatorRepository extends ServiceEntityRepository
{
    private $logger;
    private $em;
    private $settingsService;
    private $settings;

    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SettingsService $settingsService,
    ) {
        parent::__construct($registry, Operator::class);
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->settingsService      = $settingsService;
    }


    protected function operatorComparison(array $operators): array
    {
        usort(
            $operators,
            function ($a, $b) {
                $result = 0; // Default comparison result (equal)

                // Team comparison
                $teamA = $a->getTeam();
                $teamB = $b->getTeam();
                if ($teamA && $teamB && $teamA->getId() != $teamB->getId()) {
                    $result = strcmp($teamA->getName(), $teamB->getName());
                }

                // UAP comparison (only if previous comparison resulted in equality)
                if ($result == 0) {
                    $uapsA = $a->getUaps()->first();
                    $uapsB = $b->getUaps()->first();
                    if ($uapsA && $uapsB && $uapsA->getId() != $uapsB->getId()) {
                        $result = strcmp($uapsA->getName(), $uapsB->getName());
                    }
                }

                // Name comparison (only if previous comparisons resulted in equality)
                if ($result == 0) {
                    // Lower cases
                    $fullNameA = strtolower($a->getName());
                    $fullNameB = strtolower($b->getName());

                    try {
                        // Split names to separate first name and last name
                        list($firstNameA, $lastNameA) = explode('.', $fullNameA);
                        list($firstNameB, $lastNameB) = explode('.', $fullNameB);

                        // Compare last names
                        $result = strcmp($lastNameA, $lastNameB);

                        // If last names are equal, then compare first names
                        if ($result == 0) {
                            $result = strcmp($firstNameA, $firstNameB);
                        }
                    } catch (\Exception $e) {
                        // Fallback if name format is unexpected
                        $result = strcmp($fullNameA, $fullNameB);
                    }
                }

                return $result;
            }
        );

        return $operators;
    }


    public function findAllOrdered(): array
    {
        // Fetch all operators with their team and UAP
        $operators = $this->createQueryBuilder('o')
            ->join('o.team', 't')
            ->leftJoin('o.uaps', 'u')
            ->select('o, t, u')
            ->getQuery()
            ->getResult();


        return $this->operatorComparison($operators);
    }


    public function findBySearchQuery(string $name, string $code, string $team, string $uap, string $trainer)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.team', 't')
            ->leftJoin('o.uaps', 'u');

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

        if ($trainer === true) {
            $qb->setParameter('trainerStatus', true)
                ->andWhere('o.isTrainer = :trainerStatus');
        } elseif ($trainer === false) {
            $qb->setParameter('trainerStatus', false)
                ->andWhere('o.isTrainer = :trainerStatus OR o.isTrainer IS NULL');
        } elseif ($trainer === null) {
            // If $trainer is null, and you want to select all without any filter on isTrainer, do not add any where clause related to isTrainer.
            // No further action needed if you want all records regardless of trainer status.
        }
        return $this->operatorComparison($qb->getQuery()->getResult());
    }



    public function findByNameLikeForSuggestions(string $name): array
    {
        // $this->logger->info('Finding operators by name for suggestions.');

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
        # Related to Settings -> OperatorRetrainingDelay
        $operatorRetrainingDateInterval = $this->settingsService->getSettings()->getOperatorRetrainingDelay();
        $retrainingDelay = new \DateTime('now');
        $retrainingDelay->sub($operatorRetrainingDateInterval);

        $operators = $this->createQueryBuilder('o')
            ->where('o.lasttraining < :retrainingDelay')
            ->orWhere('o.lasttraining IS NULL')
            ->setParameter('retrainingDelay', $retrainingDelay)
            ->andWhere('o.tobedeleted IS NULL')
            ->andWhere('o.inactiveSince IS NULL')
            ->getQuery()
            ->getResult();

        return $operators;
    }



    // Used in the methods that check for operators to be deleted, count them, display them in appropriate views etc
    public function findInActiveOperators()
    {
        # Related to Settings -> OperatorInactivityDelay
        $operatorInactivityDateInterval = $this->settingsService->getSettings()->getOperatorInactivityDelay();
        $inactiveDelay = new \DateTime('now');
        $inactiveDelay->sub($operatorInactivityDateInterval);

        $operators = $this->createQueryBuilder('o')
            ->where('o.inactiveSince < :inactiveDelay')
            ->setParameter('inactiveDelay', $inactiveDelay)
            ->andWhere('o.tobedeleted IS NULL')
            ->getQuery()
            ->getResult();

        return $operators;
    }


    // Used in the methods that check for operators to be deleted, count them, display them in appropriate views etc
    public function findDeactivatedOperators()
    {
        # Related to Settings -> OperatorInactivityDelay
        $operatorInactivityDateInterval = $this->settingsService->getSettings()->getOperatorInactivityDelay();
        $inactiveDelay = new \DateTime('now');
        $inactiveDelay->sub($operatorInactivityDateInterval);

        $operators = $this->createQueryBuilder('o')
            ->where('o.inactiveSince < :inactiveDelay')
            ->setParameter('inactiveDelay', $inactiveDelay)
            ->andWhere('o.tobedeleted IS NOT NULL')
            ->getQuery()
            ->getResult();

        return $operators;
    }


    // Used in the methods that delete the operator entity
    public function findOperatorToBeDeleted()
    {
        # Related to Settings -> OperatorAutoDeleteDelay
        $operatorAutoDeleteDateInterval = $this->settingsService->getSettings()->getOperatorAutoDeleteDelay();
        $autoDeleteDelay = new \DateTime();
        $autoDeleteDelay->sub($operatorAutoDeleteDateInterval);

        $operatorIds = $this->createQueryBuilder('o')
            ->select('o.id')
            ->where('o.tobedeleted < :autoDeleteDelay')
            ->setParameter('autoDeleteDelay', $autoDeleteDelay)
            ->getQuery()
            ->getScalarResult();

        // Extract IDs from the result
        return array_column($operatorIds, 'id');
    }


    public function findByTeamAndUap(int $teamId, int $uapId): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.team', 't')
            ->leftJoin('o.uaps', 'u')
            ->where('t.id = :teamId')
            ->andWhere('u.id = :uapId')
            ->setParameter('teamId', $teamId)
            ->setParameter('uapId', $uapId)
            ->orderBy('t.name', 'ASC')
            ->addOrderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCodeAndTeamAndUap(string $codeOpe, int $teamId, int $uapId): ?Operator
    {
        return $this->createQueryBuilder('o')
            ->join('o.team', 't')
            ->leftJoin('o.uaps', 'u')
            ->where('o.code = :codeOpe')
            ->andWhere('t.id = :teamId')
            ->andWhere('u.id = :uapId')
            ->setParameter('codeOpe', $codeOpe)
            ->setParameter('teamId', $teamId)
            ->setParameter('uapId', $uapId)
            ->getQuery()
            ->getOneOrNullResult();
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
