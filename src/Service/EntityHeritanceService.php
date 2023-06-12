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

use App\Service\UploadsService;
use App\Service\IncidentsService;

class EntityHeritanceService
{
    private $em;
    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $uploadsService;
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
        UploadsService $uploadsService,
        IncidentCategoryRepository $incidentCategoryRepository,
        IncidentsService $incidentsService,
    ) {
        $this->em = $em;
        $this->zoneRepository = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->categoryRepository = $categoryRepository;
        $this->buttonRepository = $buttonRepository;
        $this->uploadRepository = $uploadRepository;
        $this->uploadsService = $uploadsService;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;
        $this->incidentService = $incidentsService;
    }


    public function uploadsByParentEntity($entityType, $id)
    {
        $uploads = [];

        $repository = null;
        switch ($entityType) {
            case 'zone':
                $repository = $this->zoneRepository;
                break;
            case 'productline':
                $repository = $this->productLineRepository;
                break;
                // Add other cases for other entity types
            case 'category':
                $repository = $this->categoryRepository;
                break;
            case 'button':
                $repository = $this->buttonRepository;
                break;
        }
        if (!$repository) {
            return [];
        }

        $entity = $repository->find($id);
        if (!$entity) {
            return [];
        }

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

    public function incidentsByParentEntity($entityType, $id)
    {
        $incidents = [];

        $repository = null;
        switch ($entityType) {
            case 'zone':
                $repository = $this->zoneRepository;
                break;
            case 'productline':
                $repository = $this->productLineRepository;
                break;
                // Add other cases for other entity types
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
        if (!$repository) {
            return [];
        }

        $entity = $repository->find($id);
        if (!$entity) {
            return [];
        }

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