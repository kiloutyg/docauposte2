<?php



namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentTypeRepository;

use App\Service\UploadsService;
use App\Service\IncidentsService;

class EntityDeletionService
{
    private $em;
    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $uploadsService;
    private $incidentRepository;
    private $incidentTypeRepository;
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
        IncidentTypeRepository $incidentTypeRepository,
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
        $this->incidentTypeRepository = $incidentTypeRepository;
        $this->incidentService = $incidentsService;
    }

    public function deleteEntity(string $entityType, int $id): bool
    {

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
            case 'incidentType':
                $repository = $this->incidentTypeRepository;
                break;
        }

        if (!$repository) {
            return false;
        }

        $entity = $repository->find($id);
        if (!$entity) {
            return false;
        }

        // Add deletion logic for related entities
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                $this->deleteEntity('productline', $productLine->getId());
            }
        } elseif ($entityType === 'productline') {
            foreach ($entity->getCategories() as $category) {
                $this->deleteEntity('category', $category->getId());
            }
            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
        } elseif ($entityType === 'category') {
            foreach ($entity->getButtons() as $button) {
                $this->deleteEntity('button', $button->getId());
            }
        } elseif ($entityType === 'button') {
            foreach ($entity->getUploads() as $upload) {
                $this->deleteEntity('upload', $upload->getId());
            }
        } elseif ($entityType === 'incidentType') {
            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
        } elseif ($entityType === 'upload') {
            $this->uploadsService->deleteFile($entity->getFilename(), $entity->getButton()->getId());
        } elseif ($entityType === 'incident') {
            $this->incidentService->deleteIncidentFile($entity->getName(), $entity->getProductLine());
        }
        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }
}