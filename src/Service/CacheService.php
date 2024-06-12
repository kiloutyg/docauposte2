<?php


namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\UserRepository;
use App\Repository\UploadRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\DepartmentRepository;
use App\Repository\ValidationRepository;
use App\Repository\ApprobationRepository;
use App\Repository\OldUploadRepository;
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;

class CacheService
{
    private TagAwareCacheInterface $cache;

    // Repositories
    private array $repositories;
    private array $repositoriesArray;

    private LoggerInterface $logger;

    public Collection $zones;
    public Collection $productLines;
    public Collection $categories;
    public Collection $buttons;
    public Collection $users;
    public Collection $uploads;
    public Collection $incidents;
    public Collection $incidentCategories;
    public Collection $departments;
    public Collection $validations;
    public Collection $teams;
    public Collection $operators;
    public Collection $uaps;
    public Collection $approbations;
    public Collection $oldUploads;
    public Collection $trainingRecords;
    public Collection $trainers;

    // public array $array_zones;
    // public array $array_productLines;
    // public array $array_categories;
    // public array $array_buttons;
    // public array $array_users;
    // public array $array_uploads;
    // public array $array_incidents;
    // public array $array_incidentCategories;
    // public array $array_departments;
    // public array $array_validations;
    // public array $array_teams;
    // public array $array_operators;
    // public array $array_uaps;
    // public array $array_approbations;
    // public array $array_trainingRecords;
    // public array $array_trainers;
    // public array $array_oldUploads;


    public function __construct(
        TagAwareCacheInterface          $cache,
        LoggerInterface                 $logger,
        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        UserRepository                  $userRepository,
        UploadRepository                $uploadRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        IncidentRepository              $incidentRepository,
        IncidentCategoryRepository      $incidentCategoryRepository,
        DepartmentRepository            $departmentRepository,
        ValidationRepository            $validationRepository,
        ApprobationRepository           $approbationRepository,
        OldUploadRepository             $oldUploadRepository,
        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->repositories = [
            'zones'                     => $zoneRepository,
            'productLines'              => $productLineRepository,
            'users'                     => $userRepository,
            'uploads'                   => $uploadRepository,
            'categories'                => $categoryRepository,
            'buttons'                   => $buttonRepository,
            'incidents'                 => $incidentRepository,
            'incidentCategories'        => $incidentCategoryRepository,
            'departments'               => $departmentRepository,
            'validations'               => $validationRepository,
            'teams'                     => $teamRepository,
            'operators'                 => $operatorRepository,
            'uaps'                      => $uapRepository,
            'approbations'              => $approbationRepository,
            'trainingRecords'           => $trainingRecordRepository,
            'trainers'                  => $trainerRepository,
            // 'oldUploads'                => $oldUploadRepository
        ];
        $this->initializeCollections();
    }

    private function initializeCollections(): void
    {
        foreach ($this->repositories as $key => $repository) {
            $this->{$key} = new ArrayCollection();
        }
    }


    public function cachingAppVariable(): void
    {
        foreach ($this->repositories as $key => $repository) {
            try {
                $this->{$key} = new ArrayCollection($this->cache->get("{$key}_cache", function (ItemInterface $item) use ($repository, $key) {
                    $item->tag(["{$key}_tag"]);
                    // $item->expiresAfter(43200); // Cache for 12 hours
                    $item->expiresAfter(60); // Cache for 12 hours

                    if (in_array($key, ['zones', 'productLines', 'categories', 'buttons'])) {
                        return $repository->findBy([], ['SortOrder' => 'ASC']);
                    }
                    return $repository->findBy([]);
                }));
            } catch (\Exception $e) {
                $this->logger->error("Error caching {$key}: " . $e->getMessage());
            }
        }
    }

    public function clearAndRebuildCaches(): void
    {
        foreach (array_keys($this->repositories) as $key) {
            $this->cache->delete("{$key}_cache");
        }
        $this->cachingAppVariable();
    }

    public function getEntityById(string $entityType, int $id)
    {
        $collectionName = $this->getCollectionName($entityType);
        $collection = $this->$collectionName;

        // if ($collection->isEmpty()) {
        //     $this->cachingAppVariable();
        //     $collection = $this->{$entityType};
        // }

        if ($collection->isEmpty()) {
            $this->cachingAppVariable();
            $collection = $this->{$collectionName};
        }

        $entity = $collection->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        })->first();

        return $entity === false ? null : $entity;
    }


    public function getEntityByName(string $entityType, string $name)
    {
        $collectionName = $this->getCollectionName($entityType);
        $collection = $this->$collectionName;

        if ($collection->isEmpty()) {
            $this->cachingAppVariable();
            $collection = $this->{$collectionName};
        }

        $entity = $collection->filter(function ($entity) use ($name) {
            return $entity->getName() === $name;
        })->first();

        return $entity === false ? null : $entity;
    }


    public function getEntitiesByParentId(string $entityType, int $parentId)
    {
        $collectionName = $this->getCollectionName($entityType);
        $collection = $this->$collectionName;

        if ($collection->isEmpty()) {
            $this->cachingAppVariable();
            $collection = $this->$collectionName;
        }

        switch ($collectionName) {
            case 'productLines':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getZone()->getId() === $parentId;
                });
                break;
            case 'categories':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getProductLine()->getId() === $parentId;
                });
                break;
            case 'buttons':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getCategory()->getId() === $parentId;
                });
                break;
            case 'uploads':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getButton()->getId() === $parentId;
                });
                break;
            case 'incidents':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getProductLine()->getId() === $parentId;
                });
                break;
            case 'OldUploads':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getUpload()->getId() === $parentId;
                });
                break;
            case 'validations':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getUpload()->getId() === $parentId;
                });
                if (count($entities) === 1) {
                    return $entities->first();
                }
                break;
            case 'approbations':
                $entities = $collection->filter(function ($entity) use ($parentId) {
                    return $entity->getValidation()->getId() === $parentId;
                });
                break;
            default:
                $entities = new ArrayCollection(); // Return an empty collection if the entityType is not recognized
        };

        return $entities;
    }

    private function getCollectionName(string $entityType): string
    {
        // Handle special cases for pluralization
        if ($entityType === 'category') {
            return 'categories';
        }
        return $entityType . 's';
    }
}
