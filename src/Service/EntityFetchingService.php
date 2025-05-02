<?php

namespace App\Service;

use App\Entity\Zone;
use App\Entity\ProductLine;
use App\Entity\Category;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ValidationRepository;
use App\Repository\ApprobationRepository;
use App\Repository\OldUploadRepository;
use App\Repository\OperatorRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Contracts\Cache\CacheInterface;


class EntityFetchingService extends AbstractController
{
    private $logger;

    private $cache;

    private $departmentRepository;
    private $approbationRepository;
    private $validationRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $zoneRepository;
    private $productLineRepository;
    private $userRepository;
    private $oldUploadRepository;
    private $uapRepository;
    private $teamRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $trainerRepository;

    public function __construct(
        LoggerInterface                 $logger,

        CacheInterface $cache,

        ApprobationRepository           $approbationRepository,
        ValidationRepository            $validationRepository,
        DepartmentRepository            $departmentRepository,
        IncidentCategoryRepository      $incidentCategoryRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        UploadRepository                $uploadRepository,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        UserRepository                  $userRepository,
        OldUploadRepository             $oldUploadRepository,
        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository,
        IncidentRepository              $incidentRepository,
    ) {
        $this->logger                       = $logger;

        $this->cache = $cache;

        $this->departmentRepository         = $departmentRepository;
        $this->approbationRepository        = $approbationRepository;
        $this->validationRepository         = $validationRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->userRepository               = $userRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->oldUploadRepository          = $oldUploadRepository;
        $this->uapRepository                = $uapRepository;
        $this->teamRepository               = $teamRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->trainerRepository            = $trainerRepository;
    }


    public function getUsers()
    {
        return $this->userRepository->findAll();;
    }


    public function getDepartments()
    {
        return $this->departmentRepository->findAll();
    }


    public function getZones()
    {
        return $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']);
    }


    public function getProductLines()
    {
        return $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']);
    }

    public function getProductLinesByZone(Zone $zone)
    {
        return $this->productLineRepository->findBy(['zone' => $zone->getId()], ['SortOrder' => 'ASC']);
    }


    public function getIncidents()
    {
        return $this->incidentRepository->findAll();
    }


    public function getIncidentCategories()
    {
        return $this->incidentCategoryRepository->findAll();
    }


    public function getCategories()
    {
        return $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']);
    }

    public function getCategoriesByProductLine(ProductLine $productLine)
    {
        return $this->categoryRepository->findBy(['productLine' => $productLine->getId()], ['SortOrder' => 'ASC']);
    }

    public function getButtons()
    {
        return $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']);
    }

    public function getButtonsByCategory(Category $category)
    {
        return $this->buttonRepository->findBy(['category' => $category->getId()], ['SortOrder' => 'ASC']);
    }

    public function getUploads()
    {
        return $this->uploadRepository->findAll();
    }


    public function getAllUploadsWithAssociations()
    {
        $query = $this->uploadRepository->findAllWithAssociations();
        $this->logger->info('query', $query);
        return $query;
    }


    public function getValidations()
    {
        return $this->validationRepository->findAll();
    }



    public function getAllValidatedUploadsWithAssociations()
    {
        return $this->groupUploads($this->uploadRepository->findAllValidatedUploadsWithAssociations());
    }


    public function getApprobations()
    {
        return $this->approbationRepository->findAll();
    }


    public function getOldUploads()
    {
        return $this->oldUploadRepository->findAll();
    }


    public function getTeams()
    {
        return $this->teamRepository->findAll();
    }


    public function getUaps()
    {
        return $this->uapRepository->findAll();
    }




    public function getOperators()
    {
        return $this->operatorRepository->findAllOrdered();
    }

    public function findBySearchQuery(string $name, string $code, string $team, string $uap, string $trainer): array
    {
        return $this->operatorRepository->findBySearchQuery($name, $code, $team, $uap, $trainer);
    }


    public function findOperatorWithNoRecentTraining()
    {
        return $this->operatorRepository->findOperatorWithNoRecentTraining();
    }


    public function findInActiveOperators()
    {
        return $this->operatorRepository->findInActiveOperators();
    }



    public function findDeactivatedOperators()
    {
        return $this->operatorRepository->findDeactivatedOperators();
    }


    public function findOperatorToBeDeleted()
    {
        return $this->operatorRepository->findOperatorToBeDeleted();
    }


    public function findOperatorByNameLikeForSuggestions(string $name)
    {
        return $this->operatorRepository->findByNameLikeForSuggestions($name);
    }



    public function findOperatorByCodeAndTeamAndUap(int $code, int $team, int $uap)
    {
        return $this->operatorRepository->findByCodeAndTeamAndUap($code, $team, $uap);
    }



    public function findOperatorsByTeamAndUapId(int $teamId, int $uapId): array
    {
        $selectedOperators = $this->operatorRepository->findByTeamAndUap($teamId, $uapId);
        usort($selectedOperators, function ($a, $b) {
            list($firstNameA, $surnameA) = explode('.', $a->getName());
            list($firstNameB, $surnameB) = explode('.', $b->getName());
            return $surnameA === $surnameB ? strcmp($firstNameA, $firstNameB) : strcmp($surnameA, $surnameB);
        });
        return $selectedOperators;
    }



    public function getTrainingRecords()
    {
        return $this->trainingRecordRepository->findAll();
    }


    public function getTrainers()
    {
        return $this->trainerRepository->findAll();
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


    public function findBy(string $entityType, array $criteria): mixed
    {
        return $this->{$this->fromNameToRepo($entityType)}->findBy($criteria);
    }

    public function findOneBy(string $entityType, array $criteria): mixed
    {
        return $this->{$this->fromNameToRepo($entityType)}->findOneBy($criteria);
    }

    public function count(string $entityType, array $criteria): mixed
    {
        return $this->{$this->fromNameToRepo($entityType)}->count($criteria);
    }

    public function find(string $entityType, int $entityId): mixed
    {
        return $this->{$this->fromNameToRepo($entityType)}->find($entityId);
    }

    private function fromNameToRepo(string $entityType): string
    {
        $entityTypeName = $this->checkEntityType($entityType);
        $repositoryName = $this->getRepositoryName($entityTypeName);
        return lcfirst($repositoryName);
    }


    private function getRepositoryName(string $entityType): string
    {
        $repositoryName = ucfirst($entityType) . 'Repository';

        // Create the fully qualified class name
        $repositoryClass = 'App\\Repository\\' . $repositoryName;

        if (!class_exists($repositoryClass)) {
            throw new \InvalidArgumentException(sprintf('Repository for entity type "%s" does not exist.', $entityType));
        }
        return $repositoryName;
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
