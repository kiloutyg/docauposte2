<?php



namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


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
use App\Repository\TrainerRepository;

use App\Service\IncidentService;
use App\Service\FolderService;


// This class is responsible for managing the deletion of entities, their related entities from the database
// It also refer to the logic for deleting the folder and files from the server filesystem
class EntityDeletionService
{
    private $em;
    private $logger;
    protected $projectDir;

    protected $params;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;
    private $incidentService;
    private $folderService;
    private $departmentRepository;
    private $userRepository;
    private $validationRepository;
    private $OldUploadRepository;
    private $operatorRepository;
    private $trainingRecordRepository;
    private $teamRepository;
    private $uapRepository;
    private $trainerRepository;



    public function __construct(
        EntityManagerInterface          $em,
        LoggerInterface                 $logger,

        ParameterBagInterface           $params,


        ZoneRepository                  $zoneRepository,
        ProductLineRepository           $productLineRepository,
        CategoryRepository              $categoryRepository,
        ButtonRepository                $buttonRepository,
        UploadRepository                $uploadRepository,
        IncidentRepository              $incidentRepository,
        IncidentCategoryRepository      $incidentCategoryRepository,
        IncidentService                 $incidentService,
        FolderService           $folderService,
        DepartmentRepository            $departmentRepository,
        UserRepository                  $userRepository,
        ValidationRepository            $validationRepository,
        OldUploadRepository             $OldUploadRepository,
        OperatorRepository              $operatorRepository,
        TrainingRecordRepository        $trainingRecordRepository,
        TeamRepository                  $teamRepository,
        UapRepository                   $uapRepository,
        TrainerRepository               $trainerRepository
    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;

        $this->projectDir                   = $params->get(name: 'kernel.project_dir');

        $this->zoneRepository               = $zoneRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->buttonRepository             = $buttonRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentService              = $incidentService;
        $this->folderService        = $folderService;
        $this->departmentRepository         = $departmentRepository;
        $this->userRepository               = $userRepository;
        $this->validationRepository         = $validationRepository;
        $this->OldUploadRepository          = $OldUploadRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->teamRepository               = $teamRepository;
        $this->uapRepository                = $uapRepository;
        $this->trainerRepository            = $trainerRepository;
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
            case 'productLine':
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
            case 'trainer':
                $repository = $this->trainerRepository;
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
        $this->logger->info('to be deleted entity details: ', [$entity]);

        // Deletion logic for related entities, folder and files
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                $this->deleteEntity('productLine', $productLine->getId());
            }
            $this->folderService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'productLine') {
            foreach ($entity->getCategories() as $category) {
                $this->deleteEntity('category', $category->getId());
            }

            foreach ($entity->getIncidents() as $incident) {
                $this->deleteEntity('incident', $incident->getId());
            }
            $this->folderService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'category') {
            foreach ($entity->getButtons() as $button) {
                $this->deleteEntity('button', $button->getId());
            }
            $this->folderService->deleteFolderStructure($entity->getName());
        } elseif ($entityType === 'button') {
            foreach ($entity->getUploads() as $upload) {
                $this->deleteEntity('upload', $upload->getId());
            }
            $this->folderService->deleteFolderStructure($entity->getName());
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
            $this->deleteFile($entity->getId());
        } elseif ($entityType === 'incident') {
            $this->incidentService->deleteIncidentFile($entity);
        } elseif ($entityType === 'department') {
            foreach ($entity->getUsers() as $user) {
                $entity->removeUser($user);
            }
        } elseif ($entityType === 'operator') {
            foreach ($entity->getTrainingRecords() as $trainingRecord) {
                $this->deleteEntity('trainingRecord', $trainingRecord->getId());
            }
            if ($entity->isIsTrainer()) {
                $trainerEntity = $this->trainerRepository->findOneBy(['operator' => $entity]);
                $this->logger->info('trainerEntity details: ', [$trainerEntity]);
                $this->logger->info('trainerEntity trainingRecords: ', [$trainerEntity->getTrainingRecords()]);

                if (!$trainerEntity->getTrainingRecords()->isEmpty()) {
                    $entity->setToBeDeleted(new \DateTime('now'));
                    $this->em->persist($entity);
                    $this->em->flush();
                    return false;
                } else {
                    $this->deleteEntity('trainer', $trainerEntity->getId());
                }
            }
        } elseif ($entityType === 'trainingRecord') {
            $trainer = $entity->getTrainer();
            $trainer->removeTrainingRecord($entity);
        } elseif ($entityType === 'trainer') {
            if (!$entity->getTrainingRecords()->isEmpty()) {
                return false;
            } else {
                $entity->getOperator()->setIsTrainer(false);
            }
        } elseif ($entityType === 'team') {
            $unDefinedTeam = $this->teamRepository->findOneBy(['name' => 'INDEFINI']);
            foreach ($entity->getOperator() as $operator) {
                $operator->setTeam($unDefinedTeam);
                $this->em->persist($operator);
            }
        } elseif ($entityType === 'uap') {
            $unDefinedUap = $this->uapRepository->findOneBy(['name' => 'INDEFINI']);
            foreach ($entity->getOperators() as $operator) {
                $operator->addUap($unDefinedUap);
                $this->em->persist($operator);
            }
        }
        $this->logger->info('deleted entity details: ', [$entity]);
        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }



    // This function is responsible for the logic of deleting the uploads files
    public function deleteFile(int $uploadId)
    {
        $upload     = $this->uploadRepository->findOneBy(['id' => $uploadId]);
        if ($upload->getOldUpload() != null) {
            $oldUploadId = $upload->getOldUpload()->getId();
            $this->deleteOldFile($oldUploadId);
        }
        $filename   = $upload->getFilename();
        $name       = $filename;
        $public_dir = $this->projectDir . '/public';
        $button     = $upload->getButton();

        // Dynamic folder and file deletion
        $buttonname = $button->getName();
        $parts      = explode('.', $buttonname);
        $parts      = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }
        $path = $folderPath . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
        $this->em->remove($upload);
        $this->em->flush();
        return $name;
    }


    // This function is responsible for the logic of deleting the OldUploads files
    public function deleteOldFile(int $oldUploadId)
    {

        $oldUpload = $this->OldUploadRepository->findOneBy(['id' => $oldUploadId]);

        $path = $oldUpload->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $this->em->remove($oldUpload);
        $this->em->flush();
        return;
    }
}
