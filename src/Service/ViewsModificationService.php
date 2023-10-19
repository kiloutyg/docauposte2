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
        }
        return $OriginalValue;
    }
    // 
    // 
    //     
    public function updateEntity($entityType, $entity, $field, $newValue, $originalValue)
    {
        switch ($field) {
            case 'sortOrder':
                $entity->setSortOrder($newValue);
                break;
            case 'name':

                $nameParts = explode('.', $originalValue);
                array_shift($nameParts);  // Removing the first key/value from the array
                $newName = $newValue;
                foreach ($nameParts as $namePart) {
                    $newName .= '.' . $namePart;
                }

                $this->folderCreationService->updateFolderStructureAndName($originalValue, $newName);

                $entityId = $entity->getId();
                $this->updateNameAndFolderByParentEntity($entityType, $entityId, $newName, $originalValue);

                $entity->setName($newName);
                break;
        }
        $this->em->persist($entity);
        $this->em->flush();
    }
    // 
    // 
    //     
    public function updateEntityNameInheritance($entityType, $entity, $newParentName, $originalValue)
    {
        $entityName = $entity->getName();
        $newName = $entityName . '.' . $newParentName;

        $entityId = $entity->getId();
        $this->updateNameAndFolderByParentEntity($entityType, $entityId, $newName, $originalValue);

        $entity->setName($newName);
        $this->em->persist($entity);
        $this->em->flush();
    }
    // 
    // 
    //     
    public function updateDocumentPath($entityType, $entity, $newParentName, $originalValue)
    {
        $public_dir = $this->projectDir . '/public';
        switch ($entityType) {
            case 'upload':
                // $upload = $this->uploadRepository->find($entityId);
                $upload = $entity;

                // $buttonname = $upload->getButton()->getName();
                // $parts      = explode('.', $buttonname);

                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                $folderPath = $public_dir . '/doc';
                foreach ($parts as $part) {
                    $folderPath .= '/' . $part;
                }
                $Path = $folderPath . '/' . $upload->getFilename();
                $upload->setPath($Path);
                break;

            case 'oldupload':
                // $oldUpload = $this->oldUploadRepository->find($entityId);
                $oldUpload = $entity;

                // $buttonname = $oldUpload->getButton()->getName();
                // $parts      = explode('.', $buttonname);

                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                $folderPath = $public_dir . '/doc';
                foreach ($parts as $part) {
                    $folderPath .= '/' . $part;
                }
                $Path = $folderPath . '/' . $oldUpload->getFilename();
                $oldUpload->setPath($Path);
                break;

            case 'incident':
                // $incident = $this->incidentRepository->find($entityId);
                $incident = $entity;

                // $productlineName = $incident->getProductLine()->getName();
                // $parts      = explode('.', $productlineName);

                $parts      = explode('.', $newParentName);
                $parts      = array_reverse($parts);
                $folderPath = $public_dir . '/doc';
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
    public function updateNameAndFolderByParentEntity($entityType, $id, $newName, $originalValue)
    {
        // Get the repository of the entity type
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
            case 'oldupload':
                $repository = $this->oldUploadRepository;
                break;
        }
        // If the entity type is not valid, return an empty array
        if (!$repository) {
            return [];
        }
        // Get the entity from the database and return an empty array if it doesn't exist
        $entity = $repository->find($id);
        if (!$entity) {
            return [];
        }
        // Depending on the entity type, get the related entities
        if ($entityType === 'zone') {
            foreach ($entity->getProductLines() as $productLine) {
                // $this->updateNameAndFolderByParentEntity('productline', $productLine->getId(), $newName, $originalValue);
                $this->updateEntityNameInheritance('productline', $productLine, $newName, $originalValue);
            }
        } elseif ($entityType === 'productline') {
            foreach ($entity->getIncidents() as $incident) {
                $this->updateDocumentPath('incident', $incident, $newName, $originalValue);
            }
            foreach ($entity->getCategories() as $category) {
                // $this->updateNameAndFolderByParentEntity('category', $category->getId(), $newName, $originalValue);
                $this->updateEntityNameInheritance('category', $category, $newName, $originalValue);
            }
        } elseif ($entityType === 'category') {
            foreach ($entity->getButtons() as $button) {
                // $this->updateNameAndFolderByParentEntity('button', $button->getId(), $newName, $originalValue);
                $this->updateEntityNameInheritance('button', $button, $newName, $originalValue);
            }
        } elseif ($entityType === 'button') {
            foreach ($entity->getUploads() as $upload) {
                $this->updateDocumentPath('upload', $upload, $newName, $originalValue);
            }
            foreach ($entity->getOldUploads() as $oldUpload) {
                $this->updateDocumentPath('oldupload', $oldUpload, $newName, $originalValue);
            }
        }
    }
}