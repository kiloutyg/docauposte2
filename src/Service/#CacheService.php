<?php

namespace App\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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


use App\Entity\Zone;

// class CacheService
{
    private $cache;

    // Repository methods
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

    private LoggerInterface $logger;

    public function __construct(
        CacheInterface $cache,
        // Repository methods
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


        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        // Variables related to the repositories
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


        $this->logger = $logger;
    }

    public function cachingAppVariable()
    {
        $variables = [
            'zones' => fn () => $this->zoneRepository->findBy([], ['SortOrder' => 'ASC']),
            'productLines' => fn () => $this->productLineRepository->findBy([], ['SortOrder' => 'ASC']),
            'categories' => fn () => $this->categoryRepository->findBy([], ['SortOrder' => 'ASC']),
            'buttons' => fn () => $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']),
            'users' => fn () => $this->userRepository->findAll(),
            'uploads' => fn () => $this->uploadRepository->findAll(),
            'incidents' => fn () => $this->incidentRepository->findAll(),
            'incidentCategories' => fn () => $this->incidentCategoryRepository->findAll(),
            'departments' => fn () => $this->departmentRepository->findAll(),
            'validations' => fn () => $this->validationRepository->findAll(),
            'teams' => fn () => $this->teamRepository->findAll(),
            'uaps' => fn () => $this->uapRepository->findAll(),
            'operators' => fn () => $this->operatorRepository->findAllOrdered()
        ];

        foreach ($variables as $key => $value) {
            try {
                $this->$key = $this->cache->get("{$key}_cache", function (ItemInterface $item) use ($value) {
                    $item->expiresAfter(300); // Cache for 5 min
                    return $value();
                });
            } catch (\Exception $e) {
                $this->logger->error("Error caching {$key}: " . $e->getMessage());
            }
        }
    }


    public function clearAndRebuildCaches()
    {
        // Clear the cache
        foreach (['zones', 'productLines', 'categories', 'buttons', 'uploads', 'incidents', 'incidentCategories', 'departments', 'validations', 'teams', 'operators', 'uaps'] as $key) {
            $this->cache->delete("{$key}_cache");
        }
        $this->cachingAppVariable();
    }


    public function getEntityById(string $entityType, int $id)
    {

        $collection = $this->{$entityType};

        if ($collection->isEmpty()) {
            $this->cachingAppVariable();
        }

        $entity = $collection->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        return $entity === false ? null : $entity;
    }
}
