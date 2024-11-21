<?php



namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


// use Psr\Log\LoggerInterface;


// This class is responsible for the logic of getting the related entities of a given entity
class EntityHeritanceService extends AbstractController
{

    // private $logger;

    public function __construct(

        // LoggerInterface $logger,


        // CacheService $cacheService
    ) {

        // $this->logger = $logger;

    }

    // This function returns an array of all the related uploads entities of a given entity
    public function uploadsByParentEntity($parentEntityType, $parentEntity)
    {
        $uploads = [];


        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $parentEntity->getProductLines();
            foreach ($productLines as $productLine) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('productLine', $productLine));
            }
        } elseif ($parentEntityType === 'productLine') {
            $categories = $parentEntity->getCategories();
            foreach ($categories as $category) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('category', $category));
            }
        } elseif ($parentEntityType === 'category') {
            $buttons = $parentEntity->getButtons();
            foreach ($buttons as $button) {
                $uploads = array_merge($uploads, $this->uploadsByParentEntity('button', $button));
            }
        } elseif ($parentEntityType === 'button') {
            $uploadsArray = $parentEntity->getUploads();
            $uploadsArray = $uploadsArray->toArray();
            foreach ($uploadsArray as $upload) {
                $uploads[] = $upload;
            }
        }

        return $uploads;
    }



    // This function returns an array of all the related incidents entities of a given entity
    public function incidentsByParentEntity($parentEntityType, $parentEntity)
    {
        $incidents = [];


        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $parentEntity->getProductLines();
            foreach ($productLines as $productLine) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productLine', $productLine));
            }
        } elseif ($parentEntityType === 'productLine') {
            $incidentsArray = $parentEntity->getIncidents();
            $incidentsArray = $incidentsArray->toArray();
            foreach ($incidentsArray as $incident) {
                $incidents[] = $incident;
            }
        } elseif ($parentEntityType === 'category') {
            if ($productLine = $parentEntity->getProductLine()) {
                $incidents = array_merge($incidents, $this->incidentsByParentEntity('productLine', $productLine));
            }
        }

        return $incidents;
    }



    // This function returns an array of all the related uploads entities of a given entity
    public function validatedUploadsByParentEntity($parentEntityType, $parentEntity)
    {
        $uploads = [];


        // Depending on the entity type, get the related entities
        if ($parentEntityType === 'zone') {
            $productLines = $parentEntity->getProductLines();
            foreach ($productLines as $productLine) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('productLine', $productLine->getId()));
            }
        } elseif ($parentEntityType === 'productLine') {
            $categories = $parentEntity->getCategorie();
            foreach ($categories as $category) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('category', $category->getId()));
            }
        } elseif ($parentEntityType === 'category') {
            $buttons = $parentEntity->getButtons();
            foreach ($buttons as $button) {
                $uploads = array_merge($uploads, $this->validatedUploadsByParentEntity('button', $button->getId()));
            }
        } elseif ($parentEntityType === 'button') {
            $uploadsArray = $parentEntity->getButtons();
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
