<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


use App\Repository\ButtonRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductLineRepository;
use App\Repository\ZoneRepository;
use App\Repository\UploadRepository;
use App\Repository\OldUploadRepository;
use App\Repository\IncidentRepository;
use App\Repository\UserRepository;

use App\Service\FolderCreationService;

class ViewsModificationService extends AbstractController
{
    private $em;
    private $projectDir;
    private $logger;

    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $oldUploadRepository;
    private $incidentRepository;
    private $userRepository;

    private $folderCreationService;

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $params,
        LoggerInterface $logger,

        ZoneRepository $zoneRepository,
        ProductLineRepository $productLineRepository,
        CategoryRepository $categoryRepository,
        ButtonRepository $buttonRepository,
        UploadRepository $uploadRepository,
        OldUploadRepository $oldUploadRepository,
        IncidentRepository $incidentRepository,
        UserRepository $userRepository,

        FolderCreationService $folderCreationService
    ) {
        $this->em                       = $em;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->logger                   = $logger;

        $this->zoneRepository           = $zoneRepository;
        $this->productLineRepository    = $productLineRepository;
        $this->categoryRepository       = $categoryRepository;
        $this->buttonRepository         = $buttonRepository;
        $this->uploadRepository         = $uploadRepository;
        $this->oldUploadRepository      = $oldUploadRepository;
        $this->incidentRepository       = $incidentRepository;
        $this->userRepository          = $userRepository;

        $this->folderCreationService    = $folderCreationService;
    }

    public function updateTheUpdatingOfTheSortOrder()
    {
        $zones = $this->zoneRepository->findAll();

        $zoneSortorder = 0;
        foreach ($zones as $zone) {
            $zone->setSortOrder(++$zoneSortorder);
            $this->em->persist($zone);
            $productLines = $zone->getProductLines();

            $productLineSortorder = 0;
            foreach ($productLines as $productLine) {
                $productLine->setSortOrder(++$productLineSortorder);
                $this->em->persist($productLine);
                $categories = $productLine->getCategories();

                $categoriesSortorder = 0;
                foreach ($categories as $category) {
                    $category->setSortOrder(++$categoriesSortorder);
                    $this->em->persist($category);
                    $buttons = $category->getButtons();

                    $buttonsSortorder = 0;
                    foreach ($buttons as $button) {
                        $button->setSortOrder(++$buttonsSortorder);
                        $this->em->persist($button);
                    }
                }
            }
        }
        $this->em->flush();
    }
    // 
    // 
    //     
    public function extractComponentsFromKey($key)
    {
        // Split the string by underscores
        $parts = explode('_', $key);

        // Check if there are enough parts and if the ID part is numeric
        if (count($parts) == 3 && is_numeric($parts[1])) {
            return [
                'entity' => $parts[0],
                'id'     => intval($parts[1]),
                'field'  => $parts[2]
            ];
        }
        return null;
    }
    // 
    // 
    //     
    public function defineEntityType($entityType)
    {
        // $this->logger->info('defineEntityType: entityType: ' . $entityType);
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
        }
        // If the repository is not found or the entity is not found in the database, return false
        if (!$repository) {
            return false;
        }
        return $repository;
    }
    // 
    // 
    //     
    public function defineOriginalValue($entity, $field)
    {
        $OriginalValue = null;
        switch ($field) {
            case 'sortOrder':
                $OriginalValue = $entity->getSortOrder();
                break;
            case 'name':
                $OriginalValue = $entity->getName();
                break;
            case 'creator':
                if ($entity->getCreator() === null) {
                    $OriginalValue = null;
                } else {
                    $OriginalValue = $entity->getCreator()->getId();
                }
                break;
        }
        return $OriginalValue;
    }
    // 
    // 
    //     
    public function updateEntity($entityType, $entity, $field, $newValue, $originalValue)
    {

        $entityId = $entity->getId();
        switch ($field) {
            case 'sortOrder':
                $this->updateByParentEntity($entityType, $entityId, $newValue, $field);
                break;
            case 'name':
                $entity->setName($newValue);
                $this->updateByParentEntity($entityType, $entityId, $newValue, $field);
                $this->folderCreationService->updateFolderStructureAndName($originalValue, $newValue);
                break;
            case 'creator':
                $creator = $this->userRepository->findOneBy(['id' => $newValue]);
                $entity->setCreator($creator);
                $this->updateByParentEntity($entityType, $entityId, $newValue, $field);
                break;
        }
        $this->em->persist($entity);
        $this->em->flush();
    }
    // 
    // 
    //     
    public function updateSortOrders($otherEntities, $entity, $newValue)
    {
        // $this->logger->info('entity name' . $entity->getName());
        // $this->logger->info('entity id' . $entity->getId());
        // $this->logger->info('newValue: ' . $newValue);

        $originalValue = $entity->getSortorder();
        // $this->logger->info('originalValue: ' . $originalValue);

        $entity->setSortorder($newValue);
        $entityCount = count($otherEntities);
        // $this->logger->info('entityCount: ' . $entityCount);

        // Moved to a higher position (i.e., lower value)
        if ($newValue < $originalValue) {
            foreach ($otherEntities as $otherEntity) {
                $otherSortOrder = $otherEntity->getSortOrder();
                if ($otherSortOrder >= $newValue && $otherSortOrder < $originalValue) {
                    $otherEntity->setSortOrder($otherSortOrder + 1);
                    // $this->logger->info('Incrementing sortOrder for entity with ID: ' . $otherEntity->getId());
                }
            }
        }
        // Moved to a lower position (i.e., higher value)
        elseif ($newValue > $originalValue) {
            foreach ($otherEntities as $otherEntity) {
                $otherSortOrder = $otherEntity->getSortOrder();
                if ($otherSortOrder <= $newValue && $otherSortOrder > $originalValue) {
                    $otherEntity->setSortOrder($otherSortOrder - 1);
                    // $this->logger->info('Decrementing sortOrder for entity with ID: ' . $otherEntity->getId());
                }
            }
        }
        // Set the sortOrder for the entity being changed
        $entity->setSortorder($newValue);
    }
    // 
    // 
    // 
    public function updateEntityNameInheritance($entityType, $entity, $newParentName, $field)
    {

        $entityNameParts = [];
        $entityNameParts = explode('.', $entity->getName());
        // $entityNameParts = array_reverse($entityNameParts);

        $entityName = $entityNameParts[0];
        // $this->logger->info('updateEntityNameInheritance: entityName: ' . $entityName);
        $newName = $entityName . '.' . $newParentName;
        // $this->logger->info('updateEntityNameInheritance: newName: ' . $newName);

        $entityId = $entity->getId();
        $entity->setName($newName);
        $this->em->persist($entity);
        $this->em->flush();

        $this->updateByParentEntity($entityType, $entityId, $newName, $field);
    }
    // 
    // 
    //     
    public function updateDocumentPath($entityType, $entity, $newParentName)
    {
        $public_dir = $this->projectDir . '/public';
        $folderPath = $public_dir . '/doc';

        switch ($entityType) {
            case 'upload':
                $upload = $entity;
                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                foreach ($parts as $part) {
                    $folderPath .= '/' . $part;
                }
                $Path = $folderPath . '/' . $upload->getFilename();
                $upload->setPath($Path);
                break;

            case 'oldupload':
                $oldUpload = $entity;
                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                foreach ($parts as $part) {
                    $folderPath .= '/' . $part;
                }
                $Path = $folderPath . '/' . $oldUpload->getFilename();
                $oldUpload->setPath($Path);
                break;

            case 'incident':
                $incident = $entity;
                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                foreach ($parts as $part) {
                    $folderPath .= '/' . $part;
                }
                $Path = $folderPath . '/' . $incident->getName();
                $incident->setPath($Path);
                break;
        }
    }
    // 
    //     
    //     
    public function updateByParentEntity($entityType, $id, $newName, $field, $originalValue = null)
    {

        // Get the repository of the entity type
        $repository = null;
        switch ($entityType) {
            case 'zone':
                $repository = $this->zoneRepository;
                break;
            case 'productLine':
                $repository = $this->productLineRepository;
                $parentEntityName = 'Zone';
                break;
            case 'category':
                $repository = $this->categoryRepository;
                $parentEntityName = 'ProductLine';
                break;
            case 'button':
                $repository = $this->buttonRepository;
                $parentEntityName = 'Category';
                break;
            case 'upload':
                $repository = $this->uploadRepository;
                $parentEntityName = 'Button';
                break;
            case 'incident':
                $repository = $this->incidentRepository;
                $parentEntityName = 'ProductLine';

                break;
            case 'oldupload':
                $repository = $this->oldUploadRepository;
                $parentEntityName = 'Button';
                break;
        }
        // If the entity type is not valid, return an empty array
        if (!$repository) {
            return [];
        }
        // Get the entity from the database and return an empty array if it doesn't exist
        $entity = $repository->find($id);
        // $this->logger->info('updateByParentEntity: entityName: ' . $entity->getName());
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($entityType === 'zone') {
            if ($field === 'name') {
                foreach ($entity->getProductLines() as $productLine) {
                    $this->updateEntityNameInheritance('productLine', $productLine, $newName, $field, $originalValue);
                }
            } elseif ($field === 'sortOrder') {
                $this->updateSortOrders($repository->findAllExceptOne($id), $entity, $newName, $originalValue);
            } elseif ($field === 'creator') {
                foreach ($entity->getProductLines() as $productLine) {
                    $this->updateEntity('productLine', $productLine, $field, $newName, $originalValue);
                }
            }
        } elseif ($entityType === 'productLine') {
            if ($field === 'name') {
                foreach ($entity->getCategories() as $category) {
                    $this->updateEntityNameInheritance('category', $category, $newName, $field, $originalValue);
                }
                foreach ($entity->getIncidents() as $incident) {
                    $this->updateDocumentPath('incident', $incident, $newName, $originalValue);
                }
            } elseif ($field === 'sortOrder') {
                $parentEntityId = $entity->getZone()->getId();
                $this->updateSortOrders($repository->findAllExceptOneByParent($id, $parentEntityId, $parentEntityName), $entity, $newName, $originalValue);
            }
        } elseif ($entityType === 'category') {
            if ($field === 'name') {
                foreach ($entity->getButtons() as $button) {
                    $this->updateEntityNameInheritance('button', $button, $newName, $field, $originalValue);
                }
            } elseif ($field === 'sortOrder') {
                $parentEntityId = $entity->getProductLine()->getId();
                $this->updateSortOrders($repository->findAllExceptOne($id, $parentEntityId, $parentEntityName), $entity, $newName, $originalValue);
            }
        } elseif ($entityType === 'button') {
            if ($field === 'name') {
                foreach ($entity->getUploads() as $upload) {
                    $this->updateDocumentPath('upload', $upload, $newName, $originalValue);
                }
                foreach ($entity->getOldUploads() as $oldUpload) {
                    $this->updateDocumentPath('oldupload', $oldUpload, $newName, $originalValue);
                }
            } elseif ($field === 'sortOrder') {
                $parentEntityId = $entity->getCategory()->getId();
                $this->updateSortOrders($repository->findAllExceptOneByParent($id, $parentEntityId, $parentEntityName), $entity, $newName, $originalValue);
            }
        }
    }
}
