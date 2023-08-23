<?php



namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;

use App\Service\UploadService;
use App\Service\IncidentsService;


// This class is responsible for the logic of getting the related entities of a given entity
class EntityHeritanceService
{
    private $em;
    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $uploadService;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $incidentService;

    public function __construct(
        EntityManagerInterface $em,
        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository,
        UploadRepository $uploadRepository,
        IncidentRepository $incidentRepository,
        UploadService $uploadService,
        IncidentCategoryRepository $incidentCategoryRepository,
        IncidentsService $incidentsService,
    ) {
        $this->em = $em;
        $this->zoneRepository = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->categoryRepository = $categoryRepository;
        $this->buttonRepository = $buttonRepository;
        $this->uploadRepository = $uploadRepository;
        $this->uploadService = $uploadService;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;
        $this->incidentService = $incidentsService;
    }

    // This function returns an array of all the related uploads entities of a given entity
    public function uploadsByParentEntity($entityType, $id)
    {
        $uploads = [];

        // Get the repository of the entity type
        $repository = null;
        switch ($entityType) {
            case 'zone':
                $repository = $this->zoneRepository;
                break;
            case 'productline':
                $repository = $this->productLineRepository;
                break;
            case 'category':
                $repository = $this->categoryRepository;
                break;
            case 'button':
                $repository = $this->buttonRepository;
                break;
        }
        // If the entity type is not valid, return an empty array
        if (!$repository) {
            return [];
        }
        // Get the entity from the database and return an empty array if it doesn't exist
        $entity = $repository->find($id);
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('productline', $productLine->getId()));
            }
        } elseif ($entityType === 'productline') {
            foreach ($entity->getCategories() as $category) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('category', $category->getId()));
            }
        } elseif ($entityType === 'category') {
            foreach ($entity->getButtons() as $button) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('button', $button->getId()));
            }
        } elseif ($entityType === 'button') {
            foreach ($entity->getUploads() as $upload) {
                $uploads[] = $upload;
            }
        }

        return $uploads;
    }

    // This function returns an array of all the related incidents entities of a given entity
    public function incidentsByParentEntity($entityType, $id)
    {
        $incidents = [];

        // Get the repository of the entity type
        $repository = null;
        switch ($entityType) {
            case 'zone':
                $repository = $this->zoneRepository;
                break;
            case 'productline':
                $repository = $this->productLineRepository;
                break;
            case 'category':
                $repository = $this->categoryRepository;
                break;
            case 'button':
                $repository = $this->buttonRepository;
                break;
            case 'upload':
                $repository = $this->uploadRepository;
                break;
            case 'incident':
                $repository = $this->incidentRepository;
                break;
            case 'incidentCategory':
                $repository = $this->incidentCategoryRepository;
                break;
        }
        // If the entity type is not valid, return an empty array
        if (!$repository) {
            return [];
        }
        // Get the entity from the database and return an empty array if it doesn't exist
        $entity = $repository->find($id);
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productline', $productLine->getId()));
            }
        } elseif ($entityType === 'productline') {
            foreach ($entity->getIncidents() as $incident) {
                $incidents[] = $incident;
            }
        } elseif ($entityType === 'category') {
            if ($productLineId = $entity->getProductLine()->getId()) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productline', $productLineId));
            }
        }

        return $incidents;
    }
}