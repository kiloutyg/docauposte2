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
use App\Repository\SettingsRepository;

use App\Service\SettingsService;

use App\Entity\Settings;

class CacheService
{
    private TagAwareCacheInterface $cache;

    // Repositories
    private array $repositories;
    private array $repositoriesArray;
    protected $departmentRepository;
    protected $approbationRepository;
    protected $validationRepository;
    protected $incidentRepository;
    protected $incidentCategoryRepository;
    protected $categoryRepository;
    protected $buttonRepository;
    protected $uploadRepository;
    protected $zoneRepository;
    protected $productLineRepository;
    protected $userRepository;
    protected $oldUploadRepository;
    protected $uapRepository;
    protected $teamRepository;
    protected $operatorRepository;
    protected $trainingRecordRepository;
    protected $trainerRepository;
    protected $settingsRepository;
    private $settingsService;

    private LoggerInterface $logger;

    public ?ArrayCollection $zones = null;
    public ?ArrayCollection $productLines = null;
    public ?ArrayCollection $categories = null;
    public ?ArrayCollection $buttons = null;
    public ?ArrayCollection $users = null;
    public ?ArrayCollection $uploads = null;
    public ?ArrayCollection $incidents = null;
    public ?ArrayCollection $incidentCategories = null;
    public ?ArrayCollection $departments = null;
    public ?ArrayCollection $validations = null;
    public ?ArrayCollection $teams = null;
    public ?ArrayCollection $operators = null;
    public ?ArrayCollection $uaps = null;
    public ?ArrayCollection $approbations = null;
    public ?ArrayCollection $oldUploads = null;
    public ?ArrayCollection $trainingRecords = null;
    public ?ArrayCollection $trainers = null;
    public ?Settings $settings = null;


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
        TrainerRepository               $trainerRepository,
        SettingsRepository              $settingsRepository,
        SettingsService                 $settingsService
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
            'settings'                  => $settingsRepository
        ];
        $this->zoneRepository = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->categoryRepository = $categoryRepository;
        $this->buttonRepository = $buttonRepository;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;
        $this->departmentRepository = $departmentRepository;
        $this->validationRepository = $validationRepository;
        $this->teamRepository = $teamRepository;
        $this->operatorRepository = $operatorRepository;
        $this->uapRepository = $uapRepository;
        $this->approbationRepository = $approbationRepository;
        $this->trainingRecordRepository = $trainingRecordRepository;
        $this->trainerRepository = $trainerRepository;
        $this->settingsRepository = $settingsRepository;
        $this->settingsRepository = $settingsRepository;
        $this->settingsService = $settingsService;
        // $this->initializeCollections();
        $this->cacheServiceCachingSettings();
    }

    private function initializeCollections(): void
    {
        foreach ($this->repositories as $key => $repository) {
            if ($key === 'settings') {
                continue;
            } else {
                $this->{$key} = new ArrayCollection();
            }
        }
    }

    public function cacheServiceCachingSettings()
    {
        if ($this->settings === null) {
            $this->settings = $this->cache->get("settings_cache", function (ItemInterface $item) {
                $item->tag("settings_tag");
                $item->expiresAfter(43200);
                return $this->settingsService->getSettings();
            });
        }
        return $this->settings;
    }

    public function cachingAppVariable(): void
    {
        foreach ($this->repositories as $key => $repository) {
            try {
                if ($key === 'settings') {
                    $this->cacheServiceCachingSettings();
                    continue;
                }
                $this->{$key} = new ArrayCollection($this->cache->get("{$key}_cache", function (ItemInterface $item) use ($repository, $key) {
                    $item->tag(["{$key}_tag"]);
                    $item->expiresAfter(43200); // Cache for 12 hours

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


    // Getter method for zones
    public function getZones(): ArrayCollection
    {
        if ($this->zones === null) {
            $this->zones = new ArrayCollection($this->cache->get('zones_cache', function (ItemInterface $item) {
                $item->tag('zones_tag');
                $item->expiresAfter(43200);
                return $this->repositories['zones']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->zones;
    }



    // Getter method for productLines
    public function getProductLines(): ArrayCollection
    {
        if ($this->productLines === null) {
            $this->productLines = new ArrayCollection($this->cache->get('productLines_cache', function (ItemInterface $item) {
                $item->tag('productLines_tag');
                $item->expiresAfter(43200);
                return $this->repositories['productLines']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->productLines;
    }



    // Getter method for users
    public function getUsers(): ArrayCollection
    {
        if ($this->users === null) {
            $this->users =  new ArrayCollection($this->cache->get('users_cache', function (ItemInterface $item) {
                $item->tag('users_tag');
                $item->expiresAfter(43200);
                return $this->repositories['users']->findBy([]);
            }));
        }
        return $this->users;
    }



    // Getter method for uploads
    public function getUploads(): ArrayCollection
    {
        if ($this->uploads === null) {
            $this->uploads = new ArrayCollection($this->cache->get('uploads_cache', function (ItemInterface $item) {
                $item->tag('uploads_tag');
                $item->expiresAfter(43200);
                return $this->repositories['uploads']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->uploads;
    }



    // Getter method for categories
    public function getCategories(): ArrayCollection
    {
        if ($this->categories === null) {
            $this->categories = new ArrayCollection($this->cache->get('categories_cache', function (ItemInterface $item) {
                $item->tag('categories_tag');
                $item->expiresAfter(43200);
                return $this->repositories['categories']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->categories;
    }



    // Getter method for buttons
    public function getButtons(): ArrayCollection
    {
        if ($this->buttons === null) {
            $this->buttons = new ArrayCollection($this->cache->get('buttons_cache', function (ItemInterface $item) {
                $item->tag('buttons_tag');
                $item->expiresAfter(43200);
                return $this->repositories['buttons']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->buttons;
    }




    // Getter method for incidents
    public function getIncidents(): ArrayCollection
    {
        if ($this->incidents === null) {
            $this->incidents = new ArrayCollection($this->cache->get('incidents_cache', function (ItemInterface $item) {
                $item->tag('incidents_tag');
                $item->expiresAfter(43200);
                return $this->repositories['incidents']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->incidents;
    }




    // Getter method for incidentCategories
    public function getIncidentCategories(): ArrayCollection
    {
        if ($this->incidentCategories === null) {
            $this->incidentCategories = new ArrayCollection($this->cache->get('incidentCategories_cache', function (ItemInterface $item) {
                $item->tag('incidentCategories_tag');
                $item->expiresAfter(43200);
                return $this->repositories['incidentCategories']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->incidentCategories;
    }




    // Getter method for departments
    public function getDepartments(): ArrayCollection
    {
        if ($this->departments === null) {
            $this->departments = new ArrayCollection($this->cache->get('departments_cache', function (ItemInterface $item) {
                $item->tag('departments_tag');
                $item->expiresAfter(43200);
                return $this->repositories['departments']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->departments;
    }




    // Getter method for validations
    public function getValidations(): ArrayCollection
    {
        if ($this->validations === null) {
            $this->validations = new ArrayCollection($this->cache->get('validations_cache', function (ItemInterface $item) {
                $item->tag('validations_tag');
                $item->expiresAfter(43200);
                return $this->repositories['validations']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->validations;
    }




    // Getter method for teams
    public function getTeams(): ArrayCollection
    {
        if ($this->teams === null) {
            $this->teams = new ArrayCollection($this->cache->get('teams_cache', function (ItemInterface $item) {
                $item->tag('teams_tag');
                $item->expiresAfter(43200);
                return $this->repositories['teams']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->teams;
    }




    // Getter method for operators
    public function getOperators(): ArrayCollection
    {
        if ($this->operators === null) {
            $this->operators = new ArrayCollection($this->cache->get('operators_cache', function (ItemInterface $item) {
                $item->tag('operators_tag');
                $item->expiresAfter(43200);
                return $this->repositories['operators']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->operators;
    }




    // Getter method for uaps
    public function getUaps(): ArrayCollection
    {
        if ($this->uaps === null) {
            $this->uaps = new ArrayCollection($this->cache->get('uaps_cache', function (ItemInterface $item) {
                $item->tag('uaps_tag');
                $item->expiresAfter(43200);
                return $this->repositories['uaps']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->uaps;
    }




    // Getter method for approbations
    public function getApprobations(): ArrayCollection
    {
        if ($this->approbations === null) {
            $this->approbations = new ArrayCollection($this->cache->get('approbations_cache', function (ItemInterface $item) {
                $item->tag('approbations_tag');
                $item->expiresAfter(43200);
                return $this->repositories['approbations']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->approbations;
    }



    // Getter method for trainingRecords
    public function getTrainingRecords(): ArrayCollection
    {
        if ($this->trainingRecords === null) {
            $this->trainingRecords = new ArrayCollection($this->cache->get('trainingRecords_cache', function (ItemInterface $item) {
                $item->tag('trainingRecords_tag');
                $item->expiresAfter(43200);
                return $this->repositories['trainingRecords']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->trainingRecords;
    }



    // Getter method for trainers
    public function getTrainers(): ArrayCollection
    {
        if ($this->trainers === null) {
            $this->trainers = new ArrayCollection($this->cache->get('trainers_cache', function (ItemInterface $item) {
                $item->tag('trainers_tag');
                $item->expiresAfter(43200);
                return $this->repositories['trainers']->findBy([], ['SortOrder' => 'ASC']);
            }));
        }
        return $this->trainers;
    }
}
