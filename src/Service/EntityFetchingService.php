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
use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;
use App\Repository\ShiftLeadersRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TrainerRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\ProductsRepository;
use App\Repository\QualityRepRepository;
use App\Repository\WorkstationRepository;

use Doctrine\Common\Collections\Collection;
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
    private $shiftLeadersRepository;
    private $teamRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $trainerRepository;
    private $productsRepository;
    private $qualityRepRepository;
    private $workstationRepository;

    public function __construct(
        LoggerInterface                 $logger,

        CacheInterface $cache,

        ApprobationRepository           $approbationRepository,
        ButtonRepository                $buttonRepository,
        CategoryRepository              $categoryRepository,
        DepartmentRepository            $departmentRepository,
        IncidentCategoryRepository      $incidentCategoryRepository,
        IncidentRepository              $incidentRepository,
        OldUploadRepository             $oldUploadRepository,
        OperatorRepository              $operatorRepository,
        ProductLineRepository           $productLineRepository,
        ProductsRepository              $productsRepository,
        QualityRepRepository            $qualityRepRepository,
        ShiftLeadersRepository          $shiftLeadersRepository,
        TeamRepository                  $teamRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TrainerRepository               $trainerRepository,
        UapRepository                   $uapRepository,
        UserRepository                  $userRepository,
        UploadRepository                $uploadRepository,
        ValidationRepository            $validationRepository,
        WorkstationRepository           $workstationRepository,
        ZoneRepository                  $zoneRepository,

    ) {
        $this->logger                       = $logger;

        $this->cache = $cache;

        $this->approbationRepository        = $approbationRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->departmentRepository         = $departmentRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->oldUploadRepository          = $oldUploadRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->productsRepository           = $productsRepository;
        $this->qualityRepRepository         = $qualityRepRepository;
        $this->shiftLeadersRepository       = $shiftLeadersRepository;
        $this->teamRepository               = $teamRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->trainerRepository            = $trainerRepository;
        $this->uapRepository                = $uapRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->userRepository               = $userRepository;
        $this->validationRepository         = $validationRepository;
        $this->workstationRepository        = $workstationRepository;
        $this->zoneRepository               = $zoneRepository;
    }


    public function getUsers()
    {
        return $this->userRepository->findAll();
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


    public function getAllWithAssociations()
    {
        return $this->uploadRepository->findAllWithAssociations();
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


    public function getProducts()
    {
        return $this->productsRepository->findAll();
    }

    public function getShiftLeaders()
    {
        return $this->shiftLeadersRepository->findAll();
    }

    public function getQualityRep()
    {
        return $this->qualityRepRepository->findAll();
    }

    public function getOperatorSuggestionByUsername(string $username)
    {

        $explodedUsername = explode('.', $username);
        $firstname = $explodedUsername[0] ?? null;
        $lastname  = $explodedUsername[1] ?? null;

        if ($firstname) {
            $firstnameSuggestions = $this->operatorRepository->findByNameLikeForSuggestions($firstname);
        }

        if ($lastname) {
            $lastnameSuggestions = $this->operatorRepository->findByNameLikeForSuggestions($lastname);
        }

        $rawSuggestions = array_merge($firstnameSuggestions, $lastnameSuggestions);
        $suggestions = array_unique($rawSuggestions, SORT_REGULAR);
        $response = [];
        foreach ($suggestions as &$suggestionKey) {
            if (isset($suggestionKey['id'])) {
                $response[] = $this->operatorRepository->find($suggestionKey['id']);
            }
        }

        return $response;
    }


    public function getWorkstations()
    {
        return $this->workstationRepository->findAll();
    }
}
