<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Psr\Log\LoggerInterface;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

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
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;


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


    public function getButtons()
    {
        return $this->buttonRepository->findBy([], ['SortOrder' => 'ASC']);
    }


    public function getUploads()
    {
        return $this->uploadRepository->findAll();
    }


    public function getAllWithAssociations()
    {
        $query = $this->uploadRepository->findAllWithAssociations();
        $this->logger->info('query', $query);
        return $query;
    }


    public function getNonValidatedUploads()
    {
        return $this->uploadRepository->getNonValidatedUploads();
    }


    public function getValidations()
    {
        return $this->validationRepository->findAll();
    }


    public function getValidatedUploads()
    {

        return $this->uploadRepository->getValidatedUploads();
    }


    public function getAllValidatedUploadsWithAssociations()
    {
        // This function is responsible for the logic of grouping the uploads files by parent entities
        $uploads = $this->uploadRepository->findAllValidatedUploadsWithAssociationsAtDate();

        $groupedValidatedUploads = [];
        // Group uploads by zone, productLine, category, and button
        foreach ($uploads as $upload) {

            // $this->logger->info('upload in groupAllUpload service', $upload);

            $zoneName        = $upload->getButton()->getCategory()->getProductLine()->getZone()->getName();
            $productLineName = $upload->getButton()->getCategory()->getProductLine()->getName();
            $categoryName    = $upload->getButton()->getCategory()->getName();
            $buttonName      = $upload->getButton()->getname();



            if (!isset($groupedValidatedUploads[$zoneName])) {
                $groupedValidatedUploads[$zoneName] = [];
            }
            if (!isset($groupedValidatedUploads[$zoneName][$productLineName])) {
                $groupedValidatedUploads[$zoneName][$productLineName] = [];
            }
            if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName])) {
                $groupedValidatedUploads[$zoneName][$productLineName][$categoryName] = [];
            }
            if (!isset($groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName])) {
                $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName] = [];
            }

            $groupedValidatedUploads[$zoneName][$productLineName][$categoryName][$buttonName][] = $upload;
        }


        return $groupedValidatedUploads;
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

        // $operators = $this->cache->get('operators_list', function (ItemInterface $item) {
        //     $item->expiresAfter(3600);

        //     return $this->operatorRepository->findAllOrdered();
        // });

        // return $operators;
        return $this->operatorRepository->findAllOrdered();
    }


    public function getTrainingRecords()
    {
        return $this->trainingRecordRepository->findAll();
    }


    public function getTrainers()
    {
        return $this->trainerRepository->findAll();
    }
}
