<?php



namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use Psr\Log\LoggerInterface;


// This class is responsible for the logic of getting the related entities of a given entity
class EntityHeritanceService extends AbstractController
{

    private $logger;

    public function __construct(

        LoggerInterface $logger,


        // CacheService $cacheService
    ) {

        $this->logger = $logger;

    }

    // This function returns an array of all the related uploads entities of a given entity
    /**
     * Recursively retrieves all upload entities associated with a given parent entity.
     *
     * This method traverses the entity hierarchy (zone -> productLine -> category -> button -> uploads)
     * to collect all uploads that are related to the specified parent entity, regardless of the hierarchy level.
     * The method uses recursion to navigate through the entity relationships and aggregate all uploads
     * from child entities.
     *
     * @param string $parentEntityType The type of the parent entity. Accepted values are:
     *                                 'zone', 'productLine', 'category', or 'button'
     * @param object $parentEntity The parent entity object from which to retrieve related uploads.
     *                            Must be an instance that corresponds to the specified parentEntityType
     *                            and have the appropriate getter methods (getProductLines(), getCategories(),
     *                            getButtons(), or getUploads())
     *
     * @return array An array containing all upload entities that are related to the parent entity.
     *               Returns an empty array if no uploads are found or if the parentEntityType
     *               is not recognized
     */
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
    /**
     * Recursively retrieves all incident entities associated with a given parent entity.
     *
     * This method traverses the entity hierarchy to collect all incidents that are related
     * to the specified parent entity. For zones, it retrieves incidents from all associated
     * product lines. For product lines, it directly returns the incidents. For categories,
     * it retrieves incidents from the parent product line. The method uses recursion to
     * navigate through the entity relationships and aggregate all incidents from child entities.
     *
     * @param string $parentEntityType The type of the parent entity. Accepted values are:
     *                                 'zone', 'productLine', or 'category'
     * @param object $parentEntity The parent entity object from which to retrieve related incidents.
     *                            Must be an instance that corresponds to the specified parentEntityType
     *                            and have the appropriate getter methods (getProductLines(), getIncidents(),
     *                            or getProductLine())
     *
     * @return array An array containing all incident entities that are related to the parent entity.
     *               Returns an empty array if no incidents are found or if the parentEntityType
     *               is not recognized
     */
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
    /**
     * Recursively retrieves all validated upload entities associated with a given parent entity.
     *
     * This method traverses the entity hierarchy (zone -> productLine -> category -> button -> uploads)
     * to collect all uploads that have validation and are related to the specified parent entity.
     * Only uploads with a non-null validation status are included in the results. The method uses
     * recursion to navigate through the entity relationships and aggregate all validated uploads
     * from child entities.
     *
     * @param string $parentEntityType The type of the parent entity. Accepted values are:
     *                                 'zone', 'productLine', 'category', or 'button'
     * @param object $parentEntity The parent entity object from which to retrieve related validated uploads.
     *                            Must be an instance that corresponds to the specified parentEntityType
     *                            and have the appropriate getter methods (getProductLines(), getCategorie(),
     *                            getButtons(), or getButtons() for uploads)
     *
     * @return array An array containing all validated upload entities that are related to the parent entity.
     *               Only uploads with non-null validation are included. Returns an empty array if no
     *               validated uploads are found or if the parentEntityType is not recognized
     */
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
