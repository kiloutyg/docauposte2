<?php



namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\OldUploadRepository;

use App\Service\UploadService;
use App\Service\IncidentService;
use App\Service\CacheService;


// This class is responsible for the logic of getting the related entities of a given entity
class EntityHeritanceService extends AbstractController
{
    private $em;

    private $logger;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $oldUploadRepository;

    private $uploadService;
    private $incidentService;



    private $cacheService;

    public function __construct(
        EntityManagerInterface $em,

        LoggerInterface $logger,

        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository,
        UploadRepository $uploadRepository,
        IncidentRepository $incidentRepository,
        IncidentCategoryRepository $incidentCategoryRepository,
        OldUploadRepository $oldUploadRepository,

        UploadService $uploadService,
        IncidentService $incidentService,
        CacheService $cacheService
    ) {
        $this->em = $em;

        $this->logger = $logger;

        $this->zoneRepository = $zoneRepository;
        $this->productLineRepository = $productLineRepository;
        $this->categoryRepository = $categoryRepository;
        $this->buttonRepository = $buttonRepository;
        $this->uploadRepository = $uploadRepository;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;
        $this->oldUploadRepository = $oldUploadRepository;

        $this->uploadService = $uploadService;
        $this->incidentService = $incidentService;
        $this->cacheService = $cacheService;
    }

    // This function returns an array of all the related uploads entities of a given entity
    public function uploadsByParentEntity($parentEntityType, $parentId)
    {
        $uploads = [];
        $entity = $this->cacheService->getEntityById($parentEntityType, $parentId);
        if (!$entity) {
            return [];
        }


        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $this->cacheService->getEntitiesByParentId('productLine', $parentId);
            foreach ($productLines as $productLine) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('productLine', $productLine->getId()));
            }
        } elseif ($parentEntityType === 'productLine') {
            $categories = $this->cacheService->getEntitiesByParentId('category', $parentId);
            foreach ($categories as $category) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('category', $category->getId()));
            }
        } elseif ($parentEntityType === 'category') {
            $buttons = $this->cacheService->getEntitiesByParentId('button', $parentId);
            foreach ($buttons as $button) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('button', $button->getId()));
            }
        } elseif ($parentEntityType === 'button') {
            $uploadsArray = $this->cacheService->getEntitiesByParentId('upload', $parentId);
            $uploadsArray = $uploadsArray->toArray();
            foreach ($uploadsArray as $upload) {
                $uploads[] = $upload;
            }
        }

        return $uploads;
    }



    // This function returns an array of all the related incidents entities of a given entity
    public function incidentsByParentEntity($parentEntityType, $parentId)
    {
        $incidents = [];

        $entity = $this->cacheService->getEntityById($parentEntityType, $parentId);
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $this->cacheService->getEntitiesByParentId('productLine', $parentId);
            foreach ($productLines as $productLine) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productLine', $productLine->getId()));
            }
        } elseif ($parentEntityType === 'productLine') {
            $incidentsArray = $this->cacheService->getEntitiesByParentId('incident', $parentId);
            $incidentsArray = $incidentsArray->toArray();
            foreach ($incidentsArray as $incident) {
                $incidents[] = $incident;
            }
        } elseif ($parentEntityType === 'category') {
            if ($productLineId = $entity->getProductLine()->getId()) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productLine', $productLineId));
            }
        }

        return $incidents;
    }



    // This function returns an array of all the related uploads entities of a given entity
    public function validatedUploadsByParentEntity($parentEntityType, $parentId)
    {
        $uploads = [];
        $entity = $this->cacheService->getEntityById($parentEntityType, $parentId);
        if (!$entity) {
            return [];
        }


        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $this->cacheService->getEntitiesByParentId('productLine', $parentId);
            foreach ($productLines as $productLine) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('productLine', $productLine->getId()));
            }
        } elseif ($parentEntityType === 'productLine') {
            $categories = $this->cacheService->getEntitiesByParentId('category', $parentId);
            foreach ($categories as $category) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('category', $category->getId()));
            }
        } elseif ($parentEntityType === 'category') {
            $buttons = $this->cacheService->getEntitiesByParentId('button', $parentId);
            foreach ($buttons as $button) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('button', $button->getId()));
            }
        } elseif ($parentEntityType === 'button') {
            $uploadsArray = $this->cacheService->getEntitiesByParentId('upload', $parentId);
            $uploadsArray = $uploadsArray->toArray();
            foreach ($uploadsArray as $upload) {
                if ($upload->getValidation() != null) {
                    $uploads[] = $upload;
                }
            }
        }

        return $uploads;
    }
}
