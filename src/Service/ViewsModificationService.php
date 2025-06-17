<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


use App\Service\FolderService;

use App\Service\Factory\RepositoryFactory;

class ViewsModificationService extends AbstractController
{
    private $em;
    private $projectDir;
    private $logger;

    private $repositoryFactory;
    
    private $zoneRepository;
    private $productLineRepository;
    private $categoryRepository;
    private $buttonRepository;
    private $uploadRepository;
    private $oldUploadRepository;
    private $incidentRepository;
    private $userRepository;

    private $folderService;

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $params,
        LoggerInterface $logger,
        RepositoryFactory $repositoryFactory,

        FolderService $folderService,
    ) {
        $this->em                       = $em;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->logger                   = $logger;
        $this->repositoryFactory        = $repositoryFactory;

        $this->zoneRepository           = $this->repositoryFactory->getRepository('zone');
        $this->productLineRepository    = $this->repositoryFactory->getRepository('productLine');
        $this->categoryRepository       = $this->repositoryFactory->getRepository('category');
        $this->buttonRepository         = $this->repositoryFactory->getRepository('button');
        $this->uploadRepository         = $this->repositoryFactory->getRepository('upload');
        $this->oldUploadRepository      = $this->repositoryFactory->getRepository('oldUpload');
        $this->incidentRepository       = $this->repositoryFactory->getRepository('incident');
        $this->userRepository           = $this->repositoryFactory->getRepository('user');

        $this->folderService            = $folderService;
    }

    /**
     * Updates the sort order of all entities in the hierarchy.
     *
     * This method resets and reassigns sequential sort order values for all entities
     * in the hierarchy (zones, product lines, categories, and buttons). It ensures
     * that each entity at its respective level has a unique, sequential sort order
     * starting from 1.
     *
     * The hierarchy is processed in a top-down approach:
     * 1. Zones are processed first
     * 2. For each zone, its product lines are processed
     * 3. For each product line, its categories are processed
     * 4. For each category, its buttons are processed
     *
     * @return void
     */
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



    /**
     * Extracts components from a formatted key string.
     *
     * Parses a string in the format "entity_id_field" and extracts its components.
     * The key is expected to have exactly three parts separated by underscores,
     * with the middle part being a numeric ID.
     *
     * @param string $key The formatted key string to parse (e.g., "zone_123_name")
     *
     * @return array|null Returns an associative array with 'entity', 'id', and 'field' keys
     *                    if the key is properly formatted, or null if the format is invalid
     */
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




    /**
     * Determines the appropriate repository based on the entity type.
     *
     * This method maps an entity type string to its corresponding repository object.
     * It supports 'zone', 'productLine', 'category', and 'button' entity types.
     *
     * @param string $entityType The type of entity to get the repository for
     *
     * @return mixed|false Returns the repository object if the entity type is valid,
     *                     or false if the entity type is not supported
     */
    public function defineEntityType($entityType)
    {
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




    /**
     * Retrieves the original value of a specified field from an entity.
     *
     * This method extracts the current value of a given field from an entity object.
     * It supports retrieving values for 'sortOrder', 'name', and 'creator' fields.
     * For the 'creator' field, it returns the creator's ID or null if no creator exists.
     *
     * @param object $entity The entity object to extract the value from
     * @param string $field  The field name to retrieve ('sortOrder', 'name', or 'creator')
     *
     * @return mixed The original value of the specified field, or null if the field is not supported
     */
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





    /**
     * Updates an entity's field with a new value and handles related operations.
     *
     * This method updates a specified field of an entity with a new value and performs
     * any necessary related operations based on the field type. It supports updating
     * 'sortOrder', 'name', and 'creator' fields, with different handling for each:
     * - For sortOrder: Updates the sort order in the parent entity hierarchy
     * - For name: Updates the name and propagates changes to folder structures
     * - For creator: Sets a new creator entity based on the provided ID
     *
     * @param string $entityType    The type of entity being updated ('zone', 'productLine', 'category', 'button')
     * @param object $entity        The entity object to be updated
     * @param string $field         The field to update ('sortOrder', 'name', 'creator')
     * @param mixed  $newValue      The new value to set for the field
     * @param mixed  $originalValue The original value of the field before update (used for folder renaming)
     *
     * @return void
     */
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
                $this->folderService->updateFolderStructureAndName($originalValue, $newValue);
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





    /**
     * Updates the sort order of entities when one entity's position changes.
     *
     * This method handles the reordering of entities when a specific entity's sort order
     * is changed. It ensures all entities maintain a valid, sequential sort order without
     * duplicates or gaps. If duplicate or invalid sort orders are detected, it performs
     * a complete reorganization of all entities.
     *
     * @param array  $otherEntities An array of entities excluding the one being moved
     * @param object $entity        The entity being moved to a new position
     * @param int    $newValue      The desired new sort order value for the entity
     *
     * @return void
     */
    public function updateSortOrders($otherEntities, $entity, $newValue)
    {
        $entityCount = count($otherEntities) + 1;
        $originalValue = $entity->getSortOrder();

        // Ensure newValue stays within valid bounds
        if ($newValue < 1) {
            $newValue = 1;
        }
        if ($newValue > $entityCount) {
            $newValue = $entityCount;
        }

        // First ensure all entities have valid sort orders (fix duplicates)
        $usedSortOrders = [];
        $needsReorganizing = false;

        // Add current entity's sort order to the used list
        $usedSortOrders[$originalValue] = true;

        // Check for duplicates and out-of-bounds values
        foreach ($otherEntities as $otherEntity) {
            $sortOrder = $otherEntity->getSortOrder();

            // If sort order is invalid or duplicate, mark for reorganization
            if ($sortOrder < 1 || $sortOrder > $entityCount || isset($usedSortOrders[$sortOrder])) {
                $needsReorganizing = true;
                break;
            }
            $usedSortOrders[$sortOrder] = true;
        }

        // If we found duplicates or invalid values, reassign all sort orders sequentially
        if ($needsReorganizing) {
            $currentOrder = 1;
            foreach ($otherEntities as $otherEntity) {
                if ($currentOrder == $newValue) {
                    $currentOrder++; // Skip the position we want for our target entity
                }
                $otherEntity->setSortOrder($currentOrder++);
            }
        } else {
            // If no duplicates, proceed with normal reordering
            if ($newValue < $originalValue) {
                foreach ($otherEntities as $otherEntity) {
                    $otherSortOrder = $otherEntity->getSortOrder();
                    if ($otherSortOrder >= $newValue && $otherSortOrder < $originalValue) {
                        $otherEntity->setSortOrder($otherSortOrder + 1);
                    }
                }
            } else {
                foreach ($otherEntities as $otherEntity) {
                    $otherSortOrder = $otherEntity->getSortOrder();
                    if ($otherSortOrder <= $newValue && $otherSortOrder > $originalValue) {
                        $otherEntity->setSortOrder($otherSortOrder - 1);
                    }
                }
            }
        }

        // Finally set the target entity's sort order
        $entity->setSortOrder($newValue);
    }





    /**
     * Updates an entity's name to maintain the hierarchical naming inheritance pattern.
     *
     * This method updates an entity's name when its parent's name changes, preserving
     * the hierarchical naming structure (e.g., "child.parent"). It extracts the entity's
     * base name, combines it with the new parent name, and updates the entity in the database.
     * After updating, it recursively propagates the name change to child entities.
     *
     * @param string $entityType    The type of entity being updated ('zone', 'productLine', 'category', 'button')
     * @param object $entity        The entity object whose name needs to be updated
     * @param string $newParentName The new name of the parent entity to be incorporated
     * @param string $field         The field being updated (typically 'name')
     *
     * @return void
     */
    public function updateEntityNameInheritance($entityType, $entity, $newParentName, $field)
    {

        $entityNameParts = [];
        $entityNameParts = explode('.', $entity->getName());

        $entityName = $entityNameParts[0];
        $newName = $entityName . '.' . $newParentName;

        $entityId = $entity->getId();
        $entity->setName($newName);
        $this->em->persist($entity);
        $this->em->flush();

        $this->updateByParentEntity($entityType, $entityId, $newName, $field);
    }




    /**
     * Updates the file path for document entities based on their parent's name.
     *
     * This method recalculates and updates the file path for document-related entities
     * (uploads, old uploads, and incidents) when their parent entity's name changes.
     * It constructs a new path by parsing the hierarchical parent name structure and
     * appending the entity's filename or name to create the complete file path.
     *
     * @param string $entityType     The type of document entity to update ('upload', 'oldupload', or 'incident')
     * @param object $entity         The document entity object whose path needs to be updated
     * @param string $newParentName  The new hierarchical name of the parent entity (dot-separated format)
     *
     * @return void
     */
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






    /**
     * Updates an entity and propagates changes to its child entities based on the entity type and field.
     *
     * This method handles cascading updates through the entity hierarchy when a parent entity
     * is modified. It supports different update behaviors based on the entity type (zone, productLine,
     * category, button, upload, incident, oldupload) and the field being updated (name, sortOrder, creator).
     * For name changes, it updates the hierarchical naming of child entities. For sort order changes,
     * it reorders entities within their parent context. For creator changes, it propagates the creator
     * to child entities.
     *
     * @param string $entityType    The type of entity being updated ('zone', 'productLine', 'category', 'button', etc.)
     * @param int    $id            The ID of the entity to update
     * @param mixed  $newName       The new value for the field (despite the parameter name, this is used for any field value)
     * @param string $field         The field being updated ('name', 'sortOrder', 'creator')
     * @param mixed  $originalValue The original value of the field before update (optional, used for certain operations)
     *
     * @return array An empty array if the entity or repository is not found, otherwise no explicit return value
     */
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
                $parentEntityName = 'zone';
                break;
            case 'category':
                $repository = $this->categoryRepository;
                $parentEntityName = 'productLine';
                break;
            case 'button':
                $repository = $this->buttonRepository;
                $parentEntityName = 'category';
                break;
            case 'upload':
                $repository = $this->uploadRepository;
                $parentEntityName = 'button';
                break;
            case 'incident':
                $repository = $this->incidentRepository;
                $parentEntityName = 'productLine';
                break;
            case 'oldupload':
                $repository = $this->oldUploadRepository;
                $parentEntityName = 'button';
                break;
        }
        // If the entity type is not valid, return an empty array
        if (!$repository) {
            return [];
        }
        // Get the entity from the database and return an empty array if it doesn't exist
        $entity = $repository->find($id);
        $this->logger->info('updateByParentEntity: entityName: ' . $entity->getName());
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($entityType === 'zone') {
            if ($field === 'name') {
                foreach ($entity->getProductLines() as $productLine) {
                    $this->updateEntityNameInheritance('productLine', $productLine, $newName, $field);
                }
            } elseif ($field === 'sortOrder') {
                $this->updateSortOrders($repository->findAllExceptOne($id), $entity, $newName);
            } elseif ($field === 'creator') {
                foreach ($entity->getProductLines() as $productLine) {
                    $this->updateEntity('productLine', $productLine, $field, $newName, $originalValue);
                }
            }
        } elseif ($entityType === 'productLine') {
            if ($field === 'name') {
                foreach ($entity->getCategories() as $category) {
                    $this->updateEntityNameInheritance('category', $category, $newName, $field);
                }
                foreach ($entity->getIncidents() as $incident) {
                    $this->updateDocumentPath('incident', $incident, $newName);
                }
            } elseif ($field === 'sortOrder') {
                $parentEntityId = $entity->getZone()->getId();
                $this->updateSortOrders($repository->findAllExceptOneByParent($id, $parentEntityId, $parentEntityName), $entity, $newName);
            }
        } elseif ($entityType === 'category') {
            if ($field === 'name') {
                foreach ($entity->getButtons() as $button) {
                    $this->updateEntityNameInheritance('button', $button, $newName, $field);
                }
            } elseif ($field === 'sortOrder') {
                $this->updateSortOrders($repository->findAllExceptOne($id), $entity, $newName);
            }
        } elseif ($entityType === 'button') {
            if ($field === 'name') {
                foreach ($entity->getUploads() as $upload) {
                    $this->updateDocumentPath('upload', $upload, $newName);
                }
                foreach ($entity->getOldUploads() as $oldUpload) {
                    $this->updateDocumentPath('oldupload', $oldUpload, $newName);
                }
            } elseif ($field === 'sortOrder') {
                $parentEntityId = $entity->getCategory()->getId();
                $this->updateSortOrders($repository->findAllExceptOneByParent($id, $parentEntityId, $parentEntityName), $entity, $newName);
            }
        }
    }
}
