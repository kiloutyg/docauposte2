<?php

namespace App\Repository;

use App\Entity\Operator;

use App\Service\SettingsService;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Operator>
 */

class OperatorRepository extends ServiceEntityRepository
{
    private $logger;
    private $settingsService;

    /**
     * Constructor for the OperatorRepository class.
     *
     * Initializes the repository with necessary dependencies for database operations,
     * logging, entity management, and access to application settings.
     *
     * @param ManagerRegistry $registry The registry service that manages entity managers
     * @param LoggerInterface $logger The logging service for recording operations
     * @param SettingsService $settingsService Service providing access to application settings
     */
    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger,
        SettingsService $settingsService,
    ) {
        parent::__construct($registry, Operator::class);
        $this->logger               = $logger;
        $this->settingsService      = $settingsService;
    }


    /**
     * Sorts an array of operators based on a hierarchical comparison strategy.
     *
     * This method sorts operators first by team name, then by UAP name, and finally by operator name.
     * For operator names, it attempts to parse the format "firstname.lastname" to sort by last name
     * first, then by first name. If the name format is unexpected, it falls back to comparing the
     * full names directly.
     *
     * @param array $operators An array of Operator entities to be sorted
     * @return array The sorted array of Operator entities
     */
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



    /**
     * Retrieves all operators with their associated team and UAP data in a sorted order.
     *
     * This method fetches all operators from the database along with their related team and UAP
     * entities using eager loading, then sorts them using the operatorComparison method
     * which orders by team name, UAP name, and operator name.
     *
     * @return array An ordered array of Operator entities with their related team and UAP data
     */
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


    /**
     * Searches for operators based on multiple filter criteria.
     *
     * This method builds a query to find operators matching the specified filters for name,
     * code, team, UAP, and trainer status. The results are sorted using the operatorComparison
     * method to ensure consistent ordering.
     *
     * @param string $name     Optional filter for operator name (case-insensitive partial match)
     * @param string $code     Optional filter for operator code (partial match)
     * @param string $team     Optional filter for team name (case-insensitive partial match)
     * @param string $uap      Optional filter for UAP name (case-insensitive partial match)
     * @param string $trainer  Filter for trainer status: "true" to show only trainers,
     *                         "false" to show only non-trainers, "all" to show both
     *
     * @return array An ordered array of Operator entities matching the search criteria
     */
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



    /**
     * Searches for operators whose names match or sound similar to the provided name.
     *
     * This method performs a search for operators based on a partial name match or phonetic similarity.
     * It validates the input name format, then searches using both LIKE pattern matching and SOUNDEX
     * for phonetic matching. The results include operator details along with their associated team and UAP.
     *
     * @param string $name The name or partial name to search for (must follow the format: firstname.lastname with optional hyphens)
     * @throws \InvalidArgumentException If the name format is invalid
     * @return array An associative array of operators matching the search criteria, including their team and UAP information
     */
    public function findByNameLikeForSuggestions(string $name): array
    {
        if (!preg_match('/^[a-z]+(-[a-z]+)*(\.[a-z]+(-[a-z]+)*)?$/i', $name)) {
            throw new \InvalidArgumentException("Invalid name format.");
        }

        $name = strtolower($name); // Normalize input to lower case
        $searchPattern = '%' . $name . '%'; // Create a search pattern for LIKE

        // Adjusted query for newer DBAL versions
        $sql = "SELECT o.id, o.name, o.code, t.id as team_id, t.name as team_name, u.id as uap_id, u.name as uap_name
        FROM operator o
        LEFT JOIN team t ON o.team_id = t.id
        LEFT JOIN uap_operator ou ON o.id = ou.operator_id
        LEFT JOIN uap u ON ou.uap_id = u.id
        WHERE LOWER(o.name) LIKE :pattern OR SOUNDEX(o.name) = SOUNDEX(:name)";

        return $this->getEntityManager()
            ->getConnection()
            ->executeQuery($sql, [
                'pattern' => $searchPattern,
                'name' => $name
            ])
            ->fetchAllAssociative();
    }





    // Used in the methods that add the tobedeleted datetime value in the appropriate field to the operator entity
    /**
     * Finds operators who need retraining based on the configured retraining delay.
     *
     * This method retrieves all active operators who either have never been trained
     * or whose last training date is older than the configured retraining delay period.
     * Only operators who are not marked for deletion and are currently active are included.
     *
     * @return array An array of Operator entities that require retraining
     */
    public function findOperatorWithNoRecentTraining()
    {
        # Related to Settings -> OperatorRetrainingDelay
        $operatorRetrainingDateInterval = $this->settingsService->getSettings()->getOperatorRetrainingDelay();
        $retrainingDelay = new \DateTime('now');
        $retrainingDelay->sub($operatorRetrainingDateInterval);

        return $this->createQueryBuilder('o')
            ->where('o.lasttraining < :retrainingDelay')
            ->orWhere('o.lasttraining IS NULL')
            ->setParameter('retrainingDelay', $retrainingDelay)
            ->andWhere('o.tobedeleted IS NULL')
            ->andWhere('o.inactiveSince IS NULL')
            ->getQuery()
            ->getResult();
    }



    // Used in the methods that check for operators to be deleted, count them, display them in appropriate views etc
    /**
     * Finds operators that have been inactive for longer than the configured inactivity delay.
     *
     * This method retrieves all operators whose inactivity period exceeds the configured
     * inactivity delay threshold but have not yet been marked for deletion. These operators
     * are candidates for being marked as "to be deleted" in the system.
     *
     * @return array An array of Operator entities that are inactive but not yet marked for deletion
     */
    public function findInActiveOperators()
    {
        # Related to Settings -> OperatorInactivityDelay
        $operatorInactivityDateInterval = $this->settingsService->getSettings()->getOperatorInactivityDelay();
        $inactiveDelay = new \DateTime('now');
        $inactiveDelay->sub($operatorInactivityDateInterval);

        return $this->createQueryBuilder('o')
            ->where('o.inactiveSince < :inactiveDelay')
            ->setParameter('inactiveDelay', $inactiveDelay)
            ->andWhere('o.tobedeleted IS NULL')
            ->getQuery()
            ->getResult();
    }


    // Used in the methods that check for operators to be deleted, count them, display them in appropriate views etc
    /**
     * Finds operators that have been deactivated and marked for deletion.
     *
     * This method retrieves all operators whose inactivity period exceeds the configured
     * inactivity delay threshold and have already been marked for deletion (tobedeleted field
     * is not null). These operators are in the final stage before permanent deletion.
     *
     * @return array An array of Operator entities that are deactivated and marked for deletion
     */
    public function findDeactivatedOperators()
    {
        # Related to Settings -> OperatorInactivityDelay
        $operatorInactivityDateInterval = $this->settingsService->getSettings()->getOperatorInactivityDelay();
        $inactiveDelay = new \DateTime('now');
        $inactiveDelay->sub($operatorInactivityDateInterval);

        return $this->createQueryBuilder('o')
            ->where('o.inactiveSince < :inactiveDelay')
            ->setParameter('inactiveDelay', $inactiveDelay)
            ->andWhere('o.tobedeleted IS NOT NULL')
            ->getQuery()
            ->getResult();
    }


    // Used in the methods that delete the operator entity
    /**
     * Finds operators that are marked for deletion and have exceeded the auto-delete delay period.
     *
     * This method retrieves the IDs of operators whose 'tobedeleted' timestamp is older than
     * the configured auto-delete delay period, indicating they are ready for permanent deletion
     * from the system.
     *
     * @return array An array of operator IDs that are eligible for permanent deletion
     */
    public function findOperatorToBeDeleted()
    {
        # Related to Settings -> OperatorAutoDeleteDelay
        $operatorAutoDeleteDateInterval = $this->settingsService->getSettings()->getOperatorAutoDeleteDelay();
        $autoDeleteDelay = new \DateTime();
        $autoDeleteDelay->sub($operatorAutoDeleteDateInterval);

        $operatorIds = $this->createQueryBuilder('o')
            ->select( 'o.id')
            ->where('o.tobedeleted < :autoDeleteDelay')
            ->setParameter('autoDeleteDelay', $autoDeleteDelay)
            ->getQuery()
            ->getScalarResult();

        // Extract IDs from the result
        return array_column($operatorIds, 'id');
    }




    /**
     * Finds all operators that are marked for deletion regardless of the deletion delay.
     *
     * This method retrieves all operators that have been marked for deletion (tobedeleted field
     * is not null), without considering the auto-delete delay period. This allows for retrieving
     * all operators in the deletion queue regardless of when they were marked.
     *
     * @return array An array of Operator entities that are marked for deletion
     */
    public function findOperatorToBeDeletedWithNoDelayRestriction()
    {
        return $this->createQueryBuilder('o')
            ->select( 'o')
            ->where('o.tobedeleted IS NOT NULL')
            ->getQuery()
            ->getResult();
    }



    
    /**
     * Finds all operators belonging to a specific team and UAP.
     *
     * This method retrieves all operators that are associated with both the specified team
     * and UAP (Unit of Production), ordered by team name and then UAP name.
     *
     * @param int $teamId The ID of the team to filter operators by
     * @param int $uapId The ID of the UAP to filter operators by
     *
     * @return array An array of Operator entities matching the criteria, ordered by team name and UAP name
     */
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


    /**
     * Finds an operator by their code, team ID, and UAP ID.
     *
     * This method searches for a specific operator that matches all three criteria:
     * the operator code, the team they belong to, and the UAP they are associated with.
     *
     * @param string $codeOpe The operator's code identifier
     * @param int $teamId The ID of the team the operator belongs to
     * @param int $uapId The ID of the UAP (Unit of Production) the operator is associated with
     *
     * @return Operator|null Returns the matching Operator entity if found, or null if no match exists
     */
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
}
