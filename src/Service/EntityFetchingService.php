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


    public function getUsers()
    {
        return $this->findBy('user', [[], ['username' => 'ASC']]);
    }


    public function getDepartments()
    {
        return $this->findAll('department');
    }


    public function getZones()
    {
        return $this->findBy('zone', [[], ['SortOrder' => 'ASC']]);
    }


    public function getProductLines()
    {
        return $this->findBy('productLine', [[], ['SortOrder' => 'ASC']]);
    }

    public function getProductLinesByZone(Zone $zone)
    {
        return $this->findBy('productLine', [['zone' => $zone->getId()], ['SortOrder' => 'ASC']]);
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
        return $this->findBy('category', [[], ['SortOrder' => 'ASC']]);
    }

    public function getCategoriesByProductLine(ProductLine $productLine)
    {
        return $this->findBy('category', [['productLine' => $productLine->getId()], ['SortOrder' => 'ASC']]);
    }

    public function getButtons()
    {
        return $this->findBy('button', [[], ['SortOrder' => 'ASC']]);
    }

    public function getButtonsByCategory(Category $category)
    {
        return $this->findBy('button', [['category' => $category->getId()], ['SortOrder' => 'ASC']]);
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
        return $this->groupUploads($this->fromNameToRepo('upload')->findAllValidatedUploadsWithAssociations());
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


    public function findOperatorByNameLikeForSuggestions(string $name)
    {
        return $this->fromNameToRepo('operator')->findByNameLikeForSuggestions($name);
    }



    public function findOperatorByCodeAndTeamAndUap(string $code, int $team, int $uap)
    {
        return $this->fromNameToRepo('operator')->findByCodeAndTeamAndUap($code, $team, $uap);
    }



    public function findOperatorsByTeamAndUapId(int $teamId, int $uapId): array
    {
        $selectedOperators = $this->fromNameToRepo('operator')->findByTeamAndUap($teamId, $uapId);
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

    private function groupUploads($uploads)
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

    public function getOperatorSuggestionByUsername(string $username)
    {

        $explodedUsername = explode('.', $username);
        $firstname = $explodedUsername[0] ?? null;
        $firstnameSuggestions = [];
        $lastname  = $explodedUsername[1] ?? null;
        $lastnameSuggestions = [];


        if ($firstname) {
            $firstnameSuggestions = $this->fromNameToRepo('operator')->findByNameLikeForSuggestions($firstname);
        }

        if ($lastname) {
            $lastnameSuggestions = $this->fromNameToRepo('operator')->findByNameLikeForSuggestions($lastname);
        }

        $rawSuggestions = array_merge($firstnameSuggestions, $lastnameSuggestions);
        $suggestions = array_unique($rawSuggestions, SORT_REGULAR);
        $response = [];
        foreach ($suggestions as &$suggestionKey) {
            if (isset($suggestionKey['id'])) {
                $response[] = $this->find('operator', $suggestionKey['id']);
            }
        }

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

    public function findBy(string $entityType, array $params): mixed
    {
        $criteria = $params[0] ?? [];
        $orderBy = $params[1] ?? null;
        $limit = $params[2] ?? null;
        $offset = $params[3] ?? null;

        return $this->fromNameToRepo($entityType)->findBy($criteria, $orderBy, $limit, $offset);
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
