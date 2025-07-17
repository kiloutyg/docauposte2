<?php

namespace App\Service;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Category;


use App\Service\Factory\RepositoryFactory;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Contracts\Cache\CacheInterface;


class EntityFetchingService extends AbstractController
{
    private $logger;

    private $cache;

    private $repositoryFactory;

    /**
     * Constructor for the EntityFetchingService.
     *
     * Initializes the service with required dependencies for logging, caching,
     * and repository access.
     *
     * @param LoggerInterface $logger The logger service for recording application events
     * @param CacheInterface $cache The cache service for storing and retrieving cached data
     * @param RepositoryFactory $repositoryFactory Factory service to create and access entity repositories
     */
    public function __construct(
        LoggerInterface                 $logger,

        CacheInterface                  $cache,

        RepositoryFactory               $repositoryFactory,
    ) {
        $this->logger                       = $logger;

        $this->cache                        = $cache;

        $this->repositoryFactory            = $repositoryFactory;
    }


    /**
     * Retrieves all users from the database ordered by username and then by lastname.
     *
     * This method first fetches all users sorted alphabetically by username,
     * then applies additional sorting by lastname through the repository.
     *
     * @return array An array of User entities ordered by lastname
     */
    public function getUsers()
    {
        return $this->findBy(entityType: 'user', criteria: [], orderBy: ['username' => 'ASC']);
    }


    public function getDepartments()
    {
        return $this->findAll('department');
    }


    public function getZones()
    {
        return $this->findBy(entityType: 'zone', criteria: [], orderBy: ['SortOrder' => 'ASC']);
    }


    public function getProductLines()
    {
        return $this->findBy(entityType: 'productLine', criteria: [], orderBy: ['SortOrder' => 'ASC']);
    }

    public function getProductLinesByZone(Zone $zone)
    {
        return $this->findBy(entityType: 'productLine', criteria: ['zone' => $zone->getId()], orderBy: ['SortOrder' => 'ASC']);
    }


    public function getIncidents()
    {
        return $this->findAll('incident');
    }


    public function getIncidentCategories()
    {
        return $this->findAll('incidentCategory');
    }


    public function getCategories()
    {
        return $this->findBy(entityType: 'category', criteria: [], orderBy: ['SortOrder' => 'ASC']);
    }

    public function getCategoriesByProductLine(ProductLine $productLine)
    {
        return $this->findBy(entityType: 'category', criteria: ['productLine' => $productLine->getId()], orderBy: ['SortOrder' => 'ASC']);
    }

    public function getButtons()
    {
        return $this->findBy(entityType: 'button', criteria: [], orderBy: ['SortOrder' => 'ASC']);
    }

    public function getButtonsByCategory(Category $category)
    {
        return $this->findBy(entityType: 'button', criteria: ['category' => $category->getId()], orderBy: ['SortOrder' => 'ASC']);
    }

    public function getUploads()
    {
        return $this->findAll('upload');
    }


    public function getAllUploadsWithAssociations()
    {
        return $this->fromNameToRepo('upload')->findAllWithAssociations();
    }


    public function getValidations()
    {
        return $this->findAll('validation');
    }


    public function getAllValidatedUploadsWithAssociations()
    {
        return $this->groupValidatedUploads($this->fromNameToRepo('upload')->findAllValidatedUploadsWithAssociations());
    }


    public function getApprobations()
    {
        return $this->findAll('approbation');
    }


    public function getOldUploads()
    {
        return $this->findAll('oldUpload');
    }


    public function getTeams()
    {
        return $this->findAll('team');
    }


    public function getUaps()
    {
        return $this->findAll('uap');
    }


    public function getOperators()
    {
        return $this->fromNameToRepo('operator')->findAllOrdered();
    }

    /**
     * Searches for operators based on multiple criteria.
     *
     * This method allows filtering operators by name, code, team, UAP, and trainer,
     * delegating the actual search to the operator repository.
     *
     * @param string $name The operator name to search for
     * @param string $code The operator code to filter by
     * @param string $team The team identifier to filter operators by
     * @param string $uap The UAP (Unit Assembly Production) identifier to filter operators by
     * @param string $trainer The trainer name to filter operators by
     * @return array An array of Operator entities matching the search criteria
     */
    public function findBySearchQuery(string $name, string $code, string $team, string $uap, string $trainer): array
    {
        return $this->fromNameToRepo('operator')->findBySearchQuery($name, $code, $team, $uap, $trainer);
    }


    public function findOperatorWithNoRecentTraining()
    {
        return $this->fromNameToRepo('operator')->findOperatorWithNoRecentTraining();
    }


    public function findInActiveOperators()
    {
        return $this->fromNameToRepo('operator')->findInActiveOperators();
    }



    public function findDeactivatedOperators()
    {
        return $this->fromNameToRepo('operator')->findDeactivatedOperators();
    }


    public function findOperatorToBeDeleted()
    {
        return $this->fromNameToRepo('operator')->findOperatorToBeDeleted();
    }


    public function findOperatorToBeDeletedWithNoDelayRestriction()
    {
        return $this->fromNameToRepo('operator')->findOperatorToBeDeletedWithNoDelayRestriction();
    }


    public function findOperatorByNameLikeForSuggestions(string $name)
    {
        return $this->fromNameToRepo('operator')->findByNameLikeForSuggestions($name);
    }



    public function findOperatorByCodeAndTeamAndUap(string $code, int $team, int $uap)
    {
        return $this->fromNameToRepo('operator')->findByCodeAndTeamAndUap($code, $team, $uap);
    }



    /**
     * Retrieves operators filtered by team and UAP ID and sorts them by surname then firstname.
     *
     * This method fetches operators belonging to a specific team and UAP combination,
     * then sorts them alphabetically by surname first, and by firstname when surnames match.
     * The sorting assumes operator names are in the format "firstname.surname".
     *
     * @param int $teamId The ID of the team to filter operators by
     * @param int $uapId The ID of the UAP (Unit Assembly Production) to filter operators by
     * @return array An array of sorted Operator entities matching the team and UAP criteria
     */
    public function findOperatorsByTeamAndUapId(int $teamId, int $uapId): array
    {
        $selectedOperators = $this->fromNameToRepo('operator')->findByTeamAndUap($teamId, $uapId);
        $this->logger->info('selectedOperators', [$selectedOperators]);
        usort($selectedOperators, function ($a, $b) {
            list($firstNameA, $surnameA) = explode('.', $a->getName());
            list($firstNameB, $surnameB) = explode('.', $b->getName());
            return $surnameA === $surnameB ? strcmp($firstNameA, $firstNameB) : strcmp($surnameA, $surnameB);
        });
        return $selectedOperators;
    }



    public function getTrainingRecords()
    {
        return $this->findAll('trainingRecord');
    }


    public function getTrainers()
    {
        return $this->findAll('trainer');
    }


    /**
     * Groups uploads into a hierarchical structure based on their associations.
     *
     * This method organizes uploads into a nested array structure following the hierarchy:
     * Zone -> ProductLine -> Category -> Button -> Upload
     * Uploads without complete association chain are skipped.
     *
     * @param array $uploads An array of Upload entities to be grouped
     * @return array A multi-dimensional array organizing uploads by their hierarchical associations
     */
    private function groupValidatedUploads($uploads)
    {

        $groupedValidatedUploads = [];
        foreach ($uploads as $upload) {

            $button = $upload->getButton();
            if (!$button) {
                continue;
            }

            $category = $button->getCategory();
            if (!$category) {
                continue;
            }

            $productLine = $category->getProductLine();
            if (!$productLine) {
                continue;
            }

            $zone = $productLine->getZone();
            if (!$zone) {
                continue;
            }

            $zoneName        = $zone->getName();
            $productLineName = $productLine->getName();
            $categoryName    = $category->getName();
            $buttonName      = $button->getName();

            // Using reference to build the nested array
            $ref = &$groupedValidatedUploads;
            foreach ([$zoneName, $productLineName, $categoryName, $buttonName] as $key) {
                if (!isset($ref[$key])) {
                    $ref[$key] = [];
                }
                $ref = &$ref[$key];
            }
            $ref[] = $upload;
            unset($ref);
        }

        return $groupedValidatedUploads;
    }


    public function getProducts()
    {
        return $this->findAll('products');
    }

    public function getShiftLeaders()
    {
        return $this->findAll('shiftLeaders');
    }

    public function getQualityRep()
    {
        return $this->findAll('qualityRep');
    }


    /**
     * Retrieves operator suggestions based on a username pattern.
     *
     * This method searches for operators by splitting the provided username on dots
     * and performing partial matches on both the first and last name components.
     * It expects usernames in the format "firstname.lastname" and returns unique
     * operator entities that match either the firstname or lastname portion.
     * Duplicate results are filtered out based on operator ID.
     *
     * @param string $username The username to search for, expected in "firstname.lastname" format
     * @return array An array of unique Operator entities that match the search criteria
     */
    public function getOperatorSuggestionByUsername(string $username): array
    {

        $explodedUsername = explode(separator: '.', string: $username);
        $firstname = $explodedUsername[0] ?? null;
        $firstnameSuggestions = [];
        $lastname  = $explodedUsername[1] ?? null;
        $lastnameSuggestions = [];


        if ($firstname) {
            $firstnameSuggestions = $this->fromNameToRepo(entityType: 'operator')->findByNameLikeForSuggestions($firstname);
        }

        if ($lastname) {
            $lastnameSuggestions = $this->fromNameToRepo(entityType: 'operator')->findByNameLikeForSuggestions($lastname);
        }

        $rawSuggestions = array_merge($firstnameSuggestions, $lastnameSuggestions);
        $this->logger->debug(message: 'EntityFetchingService::getOperatorSuggestionByUsername - rawSuggestions', context: [$rawSuggestions]);
        $firstFilteredSuggestions = array_unique(array: $rawSuggestions, flags: SORT_REGULAR);
        $this->logger->debug(message: 'EntityFetchingService::getOperatorSuggestionByUsername - firstFilteredSuggestions', context: [$firstFilteredSuggestions]);

        // Remove duplicates based on ID
        $suggestions = [];
        $seenIds = [];

        foreach ($firstFilteredSuggestions as $suggestion) {
            $id = $suggestion['id'];
            if (!in_array($id, $seenIds)) {
                $seenIds[] = $id;
                $suggestions[] = $suggestion;
            }
        }

        $response = [];
        foreach ($suggestions as &$suggestionKey) {
            if (isset($suggestionKey['id'])) {
                $response[] = $this->find(entityType: 'operator', entityId: $suggestionKey['id']);
            }
        }

        $this->logger->debug(message: 'EntityFetchingService::getOperatorSuggestionByUsername - final response', context: [$response]);
        return $response;
    }


    public function getWorkstations()
    {
        return $this->findAll('workstation');
    }

    public function findAll(string $entityType): array
    {
        return $this->fromNameToRepo($entityType)->findAll();
    }

    /**
     * Finds entities of the specified type that match given criteria with optional sorting and pagination.
     *
     * This method provides a flexible way to query entities by type with various filtering options.
     * It acts as a wrapper around Doctrine's repository findBy method.
     *
     * @param string $entityType The type of entity to search for (e.g., 'user', 'category')
     * @param array $params An array containing search parameters in the following order:
     *                      - [0]: criteria - Array of field conditions to filter by (default: empty array)
     *                      - [1]: orderBy - Array of field sorting instructions (default: null)
     *                      - [2]: limit - Maximum number of results to return (default: null)
     *                      - [3]: offset - Number of results to skip (default: null)
     * @return mixed An array of matching entity objects or an empty array if no matches found
     */
    public function findBy(string $entityType, array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): mixed
    {
        return $this->fromNameToRepo(entityType: $entityType)->findBy(criteria: $criteria, orderBy: $orderBy, limit: $limit, offset: $offset);
    }

    public function findOneBy(string $entityType, array $criteria): mixed
    {
        return $this->fromNameToRepo($entityType)->findOneBy($criteria);
    }

    public function count(string $entityType, array $criteria): mixed
    {
        return $this->fromNameToRepo($entityType)->count($criteria);
    }

    public function find(string $entityType, int $entityId): mixed
    {
        return $this->fromNameToRepo($entityType)->find($entityId);
    }

    private function fromNameToRepo(string $entityType): object
    {
        $entityTypeName = $this->checkEntityType($entityType);
        return $this->repositoryFactory->getRepository($entityTypeName);
    }


    /**
     * Validates and normalizes an entity type string.
     *
     * This method converts the provided entity type to proper case format
     * and verifies that the corresponding entity class exists in the application.
     *
     * @param string $entityType The raw entity type name (e.g., "operator", "category")
     * @return string The normalized entity name with proper capitalization (e.g., "Operator")
     * @throws \InvalidArgumentException If the entity class does not exist
     */
    private function checkEntityType(string $entityType): string
    {
        // Convert to proper case (e.g., "operator" to "Operator")
        $entityName = ucfirst($entityType);

        // Create the fully qualified class name
        $entityClass = 'App\\Entity\\' . $entityName;


        if (!class_exists($entityClass)) {
            throw new \InvalidArgumentException(sprintf('Entity type "%s" does not exist.', $entityName));
        }

        return $entityName;
    }
}
