<?php



namespace App\Service;

use App\Entity\ShiftLeaders;
use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\FolderService;
use App\Service\IncidentService;

use App\Repository\ButtonRepository;
use App\Repository\CategoryRepository;
use App\Repository\DepartmentRepository;
use App\Repository\IncidentCategoryRepository;
use App\Repository\IncidentRepository;
use App\Repository\OldUploadRepository;
use App\Repository\OperatorRepository;
use App\Repository\ProductLineRepository;
use App\Repository\ProductsRepository;
use App\Repository\ShiftLeadersRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainerRepository;
use App\Repository\TrainingRecordRepository;
use App\Repository\UapRepository;
use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Repository\ZoneRepository;



// This class is responsible for managing the deletion of entities, their related entities from the database
// It also refer to the logic for deleting the folder and files from the server filesystem
class EntityDeletionService
{
    private $em;
    private $logger;
    private $projectDir;

    private $folderService;
    private $incidentService;

    private $buttonRepository;
    private $categoryRepository;
    private $departmentRepository;
    private $incidentCategoryRepository;
    private $incidentRepository;
    private $OldUploadRepository;
    private $operatorRepository;
    private $productLineRepository;
    private $productsRepository;
    private $shiftLeadersRepository;
    private $teamRepository;
    private $trainerRepository;
    private $trainingRecordRepository;
    private $uapRepository;
    private $uploadRepository;
    private $userRepository;
    private $validationRepository;
    private $zoneRepository;


    public function __construct(
        EntityManagerInterface              $em,
        LoggerInterface                     $logger,

        ParameterBagInterface               $params,

        FolderService                       $folderService,
        IncidentService                     $incidentService,

        ButtonRepository                    $buttonRepository,
        CategoryRepository                  $categoryRepository,
        DepartmentRepository                $departmentRepository,
        IncidentCategoryRepository          $incidentCategoryRepository,
        IncidentRepository                  $incidentRepository,
        OldUploadRepository                 $OldUploadRepository,
        OperatorRepository                  $operatorRepository,
        ProductLineRepository               $productLineRepository,
        ProductsRepository                  $productsRepository,
        ShiftLeadersRepository              $shiftLeadersRepository,
        TeamRepository                      $teamRepository,
        TrainerRepository                   $trainerRepository,
        TrainingRecordRepository            $trainingRecordRepository,
        UapRepository                       $uapRepository,
        UploadRepository                    $uploadRepository,
        UserRepository                      $userRepository,
        ValidationRepository                $validationRepository,
        ZoneRepository                      $zoneRepository
    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;

        $this->projectDir                   = $params->get(name: 'kernel.project_dir');

        $this->folderService                = $folderService;
        $this->incidentService              = $incidentService;

        $this->buttonRepository             = $buttonRepository;
        $this->categoryRepository           = $categoryRepository;
        $this->departmentRepository         = $departmentRepository;
        $this->incidentCategoryRepository   = $incidentCategoryRepository;
        $this->incidentRepository           = $incidentRepository;
        $this->OldUploadRepository          = $OldUploadRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->productLineRepository        = $productLineRepository;
        $this->productsRepository           = $productsRepository;
        $this->shiftLeadersRepository       = $shiftLeadersRepository;
        $this->teamRepository               = $teamRepository;
        $this->trainerRepository            = $trainerRepository;
        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->uapRepository                = $uapRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->userRepository               = $userRepository;
        $this->validationRepository         = $validationRepository;
        $this->zoneRepository               = $zoneRepository;
    }

    // This function is responsible for deleting an entity and its related entities from the database and the server filesystem
    public function deleteEntity(string $entityType, int $id): bool
    {
        $this->logger->info('deleteEntity: entityType: ' . $entityType . 'id: ' . $id);

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
            case 'products':
                $repository = $this->productsRepository;
                break;
            case 'shiftLeaders':
                $repository = $this->shiftLeadersRepository;
                break;
            default:
                $this->logger->error('Invalid entity type');
                throw new \Exception('Invalid entity type');
        }
        // If the repository is not found or the entity is not found in the database, return false
        if (!$repository) {
            $this->logger->error('Repository not found or entity not found in the database');
            throw new \Exception('Repository not found or entity not found in the database');
        }
        // Get the entity from the database
        $entity = $repository->find($id);
        if (!$entity) {
            $this->logger->error('Entity not found in the database');
            throw new \Exception('Entity not found in the database');
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
                    try {
                        $this->em->persist($entity);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        $this->logger->error('Error deleting department: ', [$e->getMessage()]);
                        throw $e;
                    }
                } else {
                    $this->deleteEntity('trainer', $trainerEntity->getId());
                }
            }
        } elseif ($entityType === 'trainingRecord') {
            $trainer = $entity->getTrainer();
            $trainer->removeTrainingRecord($entity);
        } elseif ($entityType === 'trainer') {
            if (!$entity->getTrainingRecords()->isEmpty()) {
                throw new \Exception('Trainer has training records');
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
            foreach ($entity->getOperator() as $operator) {
                $operator->addUap($unDefinedUap);
                $this->em->persist($operator);
            }
        }
        $this->logger->info('deleted entity details: ', [$entity]);
        try {
            $this->em->remove($entity);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting entity: ', [$e->getMessage()]);
            throw $e;
        }
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
