<?php



namespace App\Service;

use App\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;

use App\Service\UploadsService;
use App\Service\IncidentsService;
use App\Service\FolderCreationService;

// This class is responsible for managing the deletion of entities, their related entities from the database
// It also refer to the logic for deleting the folder and files from the server filesystem
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
    private $incidentCategoryRepository;
    private $incidentService;
    private $folderCreationService;
    private $departmentRepository;
    private $userRepository;

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
        FolderCreationService $folderCreationService,
        DepartmentRepository $departmentRepository,
        UserRepository $userRepository
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
        $this->folderCreationService = $folderCreationService;
        $this->departmentRepository = $departmentRepository;
        $this->userRepository = $userRepository;
    }

    // This function is responsible for deleting an entity and its related entities from the database and the server filesystem
    public function deleteEntity(string $entityType, int $id): bool
    {
        // Get the repository for the entity type
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
            case 'department':
                $repository = $this->departmentRepository;
                break;
        }
        // If the repository is not found or the entity is not found in the database, return false
        if (!$repository) {
            return false;
        }
        // Get the entity from the database
        $entity = $repository->find($id);
        if (!$entity) {
            return false;
        }

        // Deletion logic for related entities, folder and files
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                $this->deleteEntity('productline', $productLine->getId());
            }
            $this->folderCreationService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'productline') {

            foreach ($entity->getCategories() as $category) {
                $this->deleteEntity('category', $category->getId());
            }

            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }

            $this->folderCreationService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'category') {
            foreach ($entity->getButtons() as $button) {
                $this->deleteEntity('button', $button->getId());
            }
            $this->folderCreationService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'button') {
            foreach ($entity->getUploads() as $upload) {
                $this->deleteEntity('upload', $upload->getId());
            }
            $this->folderCreationService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'incidentCategory') {
            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
        } elseif ($entityType === 'upload') {
            $this->uploadsService->deleteFile($entity->getFilename(), $entity->getButton()->getId());
        } elseif ($entityType === 'incident') {
            $this->incidentService->deleteIncidentFile($entity->getName(), $entity->getProductLine());
        } elseif ($entityType === 'department') {
            foreach ($entity->getUsers() as $user) {
                $entity->removeUser($user);
            }
        }


        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }
}