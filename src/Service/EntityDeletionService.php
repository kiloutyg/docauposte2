<?php



namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\FolderService;
use App\Service\Incident\IncidentService;
use App\Service\Factory\RepositoryFactory;


/**
 * EntityDeletionService
 *
 * This class is responsible for managing the deletion of entities and their related entities from the database.
 * It also handles the logic for deleting associated folders and files from the server filesystem.
 */
class EntityDeletionService
{
    private $em;
    private $logger;
    private $projectDir;

    private $folderService;
    private $incidentService;

    private $repositoryFactory;


    /**
     * Constructor
     *
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param ParameterBagInterface $params
     * @param FolderService $folderService
     * @param IncidentService $incidentService
     * @param RepositoryFactory $repositoryFactor
     */
    public function __construct(
        EntityManagerInterface              $em,
        LoggerInterface                     $logger,

        ParameterBagInterface               $params,

        FolderService                       $folderService,
        IncidentService                     $incidentService,

        RepositoryFactory                   $repositoryFactory

    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;

        $this->projectDir                   = $params->get(name: 'kernel.project_dir');

        $this->folderService                = $folderService;
        $this->incidentService              = $incidentService;

        $this->repositoryFactory = $repositoryFactory;
    }



    /**
     * Deletes an entity and its related entities from the database and the server filesystem.
     *
     * @param string $entityType The type of the entity to be deleted.
     * @param int $id The unique identifier of the entity to be deleted.
     *
     * @return bool|string Returns true if the entity is successfully deleted, or an error message if an exception occurs.
     *
     * @throws \InvalidArgumentException If the entity type or the entity with the given ID is not found.
     * @throws \Exception If an error occurs while deleting the entity.
     */
    public function deleteEntity(string $entityType, int $id): bool|string
    {
        $this->logger->debug('deleteEntity: entityType: ' . $entityType . 'id: ' . $id);

        $entity = $this->entityObjectRetrieving($entityType, $id);

        $this->logger->debug('to be deleted entity details: ', [$entity]);

        // Deletion logic for related entities, folder and files
        if ($entityType === 'zone') {
            $this->deleteEntityZone($entity);
        } elseif ($entityType === 'productLine') {
            $this->deleteEntityProductLine($entity);
        } elseif ($entityType === 'category') {
            $this->deleteEntityCategory($entity);
        } elseif ($entityType === 'button') {
            $this->deleteEntityButton($entity);
        } elseif ($entityType === 'user') {
            $this->deleteEntityUser($entity);
        } elseif ($entityType === 'incidentCategory') {
            $this->deleteEntityIncidentCategory($entity);
        } elseif ($entityType === 'upload') {
            $this->deleteFile($entity->getId());
        } elseif ($entityType === 'incident') {
            $this->incidentService->deleteIncidentFile($entity);
        } elseif ($entityType === 'department') {
            $this->deleteEntityDepartment($entity);
        } elseif ($entityType === 'operator') {
            $this->deleteEntityOperator($entity);
        } elseif ($entityType === 'trainingRecord') {
            $trainer = $entity->getTrainer();
            $trainer->removeTrainingRecord($entity);
        } elseif ($entityType === 'trainer') {
            $this->deleteEntityTrainer($entity);
        } elseif ($entityType === 'team') {
            $this->deleteEntityTeam($entity);
        } elseif ($entityType === 'uap') {
            $this->deleteEntityUap($entity);
        }
        $this->logger->debug('deleted entity details: ', [$entity]);
        try {
            $this->em->remove($entity);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error deleting entity: ', [$e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Deletes a Zone entity and its related entities.
     *
     * @param object $entity The Zone entity to be deleted.
     */
    private function deleteEntityZone(Object $entity): void
    {
        foreach ($entity->getProductLines() as $productLine) {
            $this->deleteEntity('productLine', $productLine->getId());
        }
        $this->folderService->deleteFolderStructure($entity->getName());
    }

    /**
     * Deletes a ProductLine entity and its related entities.
     *
     * @param object $entity The ProductLine entity to be deleted.
     */
    private function deleteEntityProductLine(Object $entity): void
    {
        foreach ($entity->getCategories() as $category) {
            $this->deleteEntity('category', $category->getId());
        }
        foreach ($entity->getIncidents() as $incident) {
            $this->deleteEntity('incident', $incident->getId());
        }
        $this->folderService->deleteFolderStructure($entity->getName());
    }


    /**
     * Deletes a Category entity and its related entities.
     *
     * @param object $entity The Category entity to be deleted.
     */
    private function deleteEntityCategory(Object $entity): void
    {
        foreach ($entity->getButtons() as $button) {
            $this->deleteEntity('button', $button->getId());
        }
        $this->folderService->deleteFolderStructure($entity->getName());
    }


    /**
     * Deletes a Button entity and its related entities.
     *
     * @param object $entity The Button entity to be deleted.
     */
    private function deleteEntityButton(Object $entity): void
    {
        foreach ($entity->getUploads() as $upload) {
            $this->deleteEntity('upload', $upload->getId());
        }
        $this->folderService->deleteFolderStructure($entity->getName());
    }


    /**
     * Deletes a User entity and its related entities.
     *
     * @param object $entity The User entity to be deleted.
     */
    private function deleteEntityUser(Object $entity): void
    {
        foreach ($entity->getUploads() as $upload) {
            $this->deleteEntity('upload', $upload->getId());
        }
        foreach ($entity->getIncidents() as $incident) {
            $this->deleteEntity('incident', $incident->getId());
        }
    }


    /**
     * Deletes an IncidentCategory entity and its related entities.
     *
     * @param object $entity The IncidentCategory entity to be deleted.
     */
    private function deleteEntityIncidentCategory(Object $entity): void
    {
        foreach ($entity->getIncidents() as $incident) {
            $this->deleteEntity('incident', $incident->getId());
        }
    }


    /**
     * Deletes a Department entity and its related entities.
     *
     * @param object $entity The Department entity to be deleted.
     */
    private function deleteEntityDepartment(Object $entity): void
    {
        foreach ($entity->getUsers() as $user) {
            $entity->removeUser($user);
        }
    }


    /**
     * Deletes an Operator entity and its related entities.
     *
     * @param object $entity The Operator entity to be deleted.
     */
    private function deleteEntityOperator(Object $entity): void
    {
        foreach ($entity->getTrainingRecords() as $trainingRecord) {
            $this->deleteEntity('trainingRecord', $trainingRecord->getId());
        }
        if ($entity->isIsTrainer()) {
            $repository = $this->repositoryFactory->getRepository('trainer');
            $trainerEntity = $repository->findOneBy(['operator' => $entity]);
            $this->logger->debug('trainerEntity details: ', [$trainerEntity]);
            $this->logger->debug('trainerEntity trainingRecords: ', [$trainerEntity->getTrainingRecords()]);
            if (!$trainerEntity->getTrainingRecords()->isEmpty()) {
                $entity->setToBeDeleted(new \DateTime('now'));
            } else {
                $this->deleteEntity('trainer', $trainerEntity->getId());
            }
        }
    }


    /**
     * Deletes a Trainer entity and its related entities.
     *
     * @param object $entity The Trainer entity to be deleted.
     * @throws \InvalidArgumentException If the trainer has training records.
     */
    private function deleteEntityTrainer(Object $entity): void
    {
        if (!$entity->getTrainingRecords()->isEmpty()) {
            throw new \InvalidArgumentException('Trainer has training records');
        } else {
            $entity->getOperator()->setIsTrainer(false);
        }
    }


    /**
     * Deletes a Team entity and reassigns its operators to an undefined team.
     *
     * @param object $entity The Team entity to be deleted.
     */
    private function deleteEntityTeam(Object $entity): void
    {
        $repository = $this->repositoryFactory->getRepository('team');
        $unDefinedTeam = $repository->findOneBy(['name' => 'INDEFINI']);
        foreach ($entity->getOperator() as $operator) {
            $operator->setTeam($unDefinedTeam);
            $this->em->persist($operator);
        }
    }


    /**
     * Deletes a UAP entity and reassigns its operators to an undefined UAP.
     *
     * @param object $entity The UAP entity to be deleted.
     */
    private function deleteEntityUap(Object $entity): void
    {
        $repository = $this->repositoryFactory->getRepository('uap');

        $unDefinedUap = $repository->findOneBy(['name' => 'INDEFINI']);
        foreach ($entity->getOperators() as $operator) {
            foreach ($entity->getOperator() as $operator) {
                $operator->addUap($unDefinedUap);
                $this->em->persist($operator);
            }
        }
    }




    /**
     * Deletes an upload file and its associated database record.
     *
     * @param int $uploadId The ID of the upload to be deleted.
     * @return string The name of the deleted file.
     */
    public function deleteFile(
        int $uploadId
    ) {
        $repository = $this->repositoryFactory->getRepository('upload');

        $upload     = $repository->findOneBy(['id' => $uploadId]);
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


    /**
     * Deletes an old upload file and its associated database record.
     *
     * @param int $oldUploadId The ID of the old upload to be deleted.
     */
    public function deleteOldFile(
        int $oldUploadId
    ) {
        $repository     = $this->repositoryFactory->getRepository('oldUpload');
        $oldUpload      = $repository->findOneBy(['id' => $oldUploadId]);

        $path = $oldUpload->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $this->em->remove($oldUpload);
        $this->em->flush();
    }


    /**
     * Retrieves an entity object based on its type and ID.
     *
     * @param string $entityType The type of the entity to retrieve.
     * @param int $id The ID of the entity to retrieve.
     * @return object|bool The retrieved entity object or false if not found.
     * @throws \InvalidArgumentException If the repository for the entity type is not found or the entity is not found in the database.
     */
    public function entityObjectRetrieving(
        string $entityType,
        int $id
    ): Object|Bool {

        // Get the repository for the entity type
        $repository = null;

        $repository = $this->repositoryFactory->getRepository($entityType);
        $entity = $repository->find($id);
        if (!$entity) {
            $this->logger->error('Entity not found in the database');
            throw new \InvalidArgumentException('Entity not found in the database');
        }

        return $entity;
    }
}
