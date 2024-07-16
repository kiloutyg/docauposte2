<?php



namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use App\Repository\ZoneRepository;
use App\Repository\ProductLineRepository;
use App\Repository\CategoryRepository;
use App\Repository\ButtonRepository;
use App\Repository\UploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Repository\OldUploadRepository;
use App\Repository\OperatorRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\TeamRepository;
use App\Repository\UapRepository;

use App\Service\UploadService;
use App\Service\OldUploadService;
use App\Service\IncidentService;
use App\Service\FolderCreationService;


// This class is responsible for managing the deletion of entities, their related entities from the database
// It also refer to the logic for deleting the folder and files from the server filesystem
class EntityDeletionService
{
    private $em;
    private $logger;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $uploadService;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $incidentService;
    private $folderCreationService;
    private $departmentRepository;
    private $userRepository;
    private $validationRepository;
    private $OldUploadRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $teamRepository;
    private $uapRepository;



    public function __construct(
        EntityManagerInterface          $em,
        LoggerInterface                 $logger,

        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        UploadRepository                $uploadRepository,
        IncidentRepository              $incidentRepository,
        UploadService                   $uploadService,
        IncidentCategoryRepository      $incidentCategoryRepository,
        IncidentService                 $incidentService,
        FolderCreationService           $folderCreationService,
        DepartmentRepository            $departmentRepository,
        UserRepository                  $userRepository,
        ValidationRepository            $validationRepository,
        OldUploadRepository             $OldUploadRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TeamRepository                  $teamRepository,
        UapRepository                   $uapRepository
    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;

        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->uploadService                = $uploadService;
        $this->incidentRepository           = $incidentRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentService              = $incidentService;
        $this->folderCreationService        = $folderCreationService;
        $this->departmentRepository         = $departmentRepository;
        $this->userRepository               = $userRepository;
        $this->validationRepository         = $validationRepository;
        $this->OldUploadRepository          = $OldUploadRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->teamRepository                = $teamRepository;
        $this->uapRepository                = $uapRepository;
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
            case 'user':
                $repository = $this->userRepository;
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
            case 'validation':
                $repository = $this->validationRepository;
                break;
            case 'oldUpload':
                $repository = $this->OldUploadRepository;
                break;
            case 'operator':
                $repository = $this->operatorRepository;
                break;
            case 'trainingRecord':
                $repository = $this->trainingRecordRepository;
                break;
            case 'team':
                $repository = $this->teamRepository;
                break;
            case 'uap':
                $repository = $this->uapRepository;
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
        } elseif ($entityType === 'user') {
            foreach ($entity->getUploads() as $upload) {
                $this->deleteEntity('upload', $upload->getId());
            }
            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
        } elseif ($entityType === 'incidentCategory') {
            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
        } elseif ($entityType === 'upload') {
            foreach ($entity->getTrainingRecords() as $trainingRecord) {
                $this->deleteEntity('trainingRecord', $trainingRecord->getId());
            }
            $this->uploadService->deleteFile($entity->getId());
            // $this->deleteEntity('validation', $entity->getValidation()->getId());
        } elseif ($entityType === 'incident') {
            $this->incidentService->deleteIncidentFile($entity, $entity->getProductLine());
        } elseif ($entityType === 'department') {
            foreach ($entity->getUsers() as $user) {
                $entity->removeUser($user);
            }
        } elseif ($entityType === 'operator') {
            foreach ($entity->getTrainingRecords() as $trainingRecord) {
                $this->deleteEntity('trainingRecord', $trainingRecord->getId());
            }
        } elseif ($entityType === 'trainingRecord') {
            $trainer = $entity->getTrainer();
            $trainer->removeTrainingRecord($entity);
        } elseif ($entityType === 'team') {
            $unDefinedTeam = $this->teamRepository->findOneBy(['name' => 'INDEFINI']);
            $this->logger->info('UnDefined Team: ', [$unDefinedTeam]);
            foreach ($entity->getOperator() as $operator) {
                $operator->setTeam($unDefinedTeam);
                $this->em->persist($operator);
            }
        } elseif ($entityType === 'uap') {
            $unDefinedUap = $this->uapRepository->findOneBy(['name' => 'INDEFINI']);
            $this->logger->info('UnDefined UAP: ', [$unDefinedUap]);
            foreach ($entity->getOperator() as $operator) {
                $operator->setUap($unDefinedUap);
                $this->em->persist($operator);
            }
        }

        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }
}
