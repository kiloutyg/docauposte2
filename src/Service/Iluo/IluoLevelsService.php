<?php

namespace App\Service\Iluo;

use App\Entity\IluoLevels;

use App\Repository\IluoLevelsRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\Form;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Psr\Log\LoggerInterface;

class IluoLevelsService extends AbstractController
{

    private $em;
    private $logger;

    private $iluoLevelsRepository;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        IluoLevelsRepository $iluoLevelsRepository
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->iluoLevelsRepository = $iluoLevelsRepository;
    }



    /**
     * Processes the ILUO levels creation form by extracting data, formatting it, and persisting to database.
     *
     * This method handles the complete workflow of processing a submitted ILUO levels form:
     * - Extracts the form data
     * - Converts the name to uppercase for consistency
     * - Persists the entity to the database
     * - Returns the processed name regardless of success or failure
     *
     * @param Form $iluoLevelsForm The submitted form containing ILUO levels data to be processed
     *
     * @return string The uppercase name of the ILUO level that was processed
     */
    public function iluoLevelsCreationFormProcessing(Form $iluoLevelsForm): string
    {
        $this->logger->debug(message: 'iluoLevelsService::iluoLevelsCreationFormProcessing - Processing iluoLevels creation form', context: [$iluoLevelsForm]);
        try {
            $iluoLevelsData = $iluoLevelsForm->getData();
            $iluoLevelsData->setLevel(strtoupper($iluoLevelsData->getLevel()));

            $this->em->persist($iluoLevelsData);
            $this->em->flush();
        } finally {

            $priorityOrder = $iluoLevelsData->getPriorityOrder();
            $this->logger->debug(message: 'iluoLevelsService::iluoLevelsCreationFormProcessing - Priority order updated', context: ['priorityOrder' => $priorityOrder]);
            if ($this->iluoLevelsRepository->priorityOrderExists($priorityOrder)) {
                $this->logger->debug(message: 'iluoLevelsService::iluoLevelsCreationFormProcessing - Priority order already exists', context: ['priorityOrder' => $priorityOrder]);
                $this->updatePriorityOrders(
                    otherEntities: $this->iluoLevelsRepository->findAllExceptOne($iluoLevelsData->getId()),
                    entity: $iluoLevelsData,
                    newValue: $priorityOrder
                );
            }
            return $iluoLevelsData->getLevel();
        }
    }




    /**
     * Updates the priority order of an entity within a collection of other entities.
     *
     * This function reorganizes the priority orders of entities based on a new value provided.
     * It checks for duplicates and invalid values, and reassigns all priority orders sequentially if necessary.
     * If no duplicates are found, it proceeds with normal reordering.
     * Finally, it sets the target entity's sort order and flushes the changes to the database.
     *
     * @param array $otherEntities An array of other entities to consider when reordering
     * @param IluoLevels $entity The entity whose priority order needs to be updated
     * @param int $newValue The new priority order value to assign to the entity
     *
     * @return void
     */
    public function updatePriorityOrders(array $otherEntities, IluoLevels $entity, int $newValue)
    {
        $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Updating priority order', context: ['newValue' => $newValue]);

        $entityCount = count($otherEntities) + 1;
        $originalValue = $entity->getPriorityOrder();

        $newValue = $this->newValueAssert($newValue, $entityCount);

        // If we found duplicates or invalid values, reassign all priority orders sequentially
        if ($this->determineIfReorganizingIsNeeded(originalValue: $originalValue, otherEntities: $otherEntities, entityCount: $entityCount)) {
            $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Reorganizing priority orders due to duplicates or invalid values');
            $this->reorganizeAllPriorityOrders(otherEntities: $otherEntities, newValue: $newValue);
        } else {
            $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - No total reorganizing needed due to duplicates or invalid values');
            // If no duplicates, proceed with normal reordering
            $this->reorganizeSpecificPriorityOrder(otherEntities: $otherEntities, newValue: $newValue, originalValue: $originalValue);
        }

        // Finally set the target entity's sort order
        $entity->setPriorityOrder($newValue);
        $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Priority order updated', context: ['entityId' => $entity->getId(), 'newValue' => $newValue]);
        $this->em->flush();
    }


    /**
     * Ensures the new value for priority order stays within valid bounds.
     *
     * This function checks if the provided new value falls within the range of 1 to the total number of entities.
     * If the new value is less than 1, it is set to 1. If it exceeds the total number of entities, it is set to the total number of entities.
     *
     * @param int $newValue The new value for priority order to be checked and potentially adjusted
     * @param int $entityCount The total number of entities
     *
     * @return int The adjusted new value for priority order, ensuring it stays within valid bounds
     */
    private function newValueAssert(int $newValue, int $entityCount)
    {
        // Ensure newValue stays within valid bounds
        if ($newValue < 1) {
            $newValue = 1;
        }
        if ($newValue > $entityCount) {
            $newValue = $entityCount;
        }
        return $newValue;
    }

    /**
     * Determines if reorganizing of priority orders is needed.
     *
     * This function checks if there are any duplicates or invalid values in the priority orders of other entities.
     * It also ensures that the current entity's priority order is within valid bounds.
     *
     * @param int $originalValue The original priority order of the current entity
     * @param array $otherEntities An array of other entities to consider when checking for reorganization
     * @param int $entityCount The total number of entities
     *
     * @return bool True if reorganizing is needed, false otherwise
     */
    private function determineIfReorganizingIsNeeded(int $originalValue, array $otherEntities, int $entityCount): bool
    {
        // First ensure all entities have valid sort orders (fix duplicates)
        $usedPriorityOrders = [];
        $needsReorganizing = false;

        // Add current entity's sort order to the used list
        $usedPriorityOrders[$originalValue] = true;

        // Check for duplicates and out-of-bounds values
        foreach ($otherEntities as $otherEntity) {
            $priorityOrder = $otherEntity->getPriorityOrder();

            // If sort order is invalid or duplicate, mark for reorganization
            if ($priorityOrder < 1 || $priorityOrder > $entityCount || isset($usedPriorityOrders[$priorityOrder])) {
                $needsReorganizing = true;
                break;
            }
            $usedPriorityOrders[$priorityOrder] = true;
        }
        return $needsReorganizing;
    }


    /**
     * Reorganizes the priority orders of all other entities when a new entity is created or an existing entity's priority order is updated.
     *
     * This function iterates through the provided array of other entities and reassigns their priority orders.
     * If the new value is equal to the current order of an entity, it skips that position to avoid conflicts.
     * It also logs the reassignment of priority orders for debugging purposes.
     *
     * @param array $otherEntities An array of other entities to consider when reordering
     * @param int $newValue The new priority order value to assign to the entity
     *
     * @return void
     */
    private function reorganizeAllPriorityOrders(array $otherEntities, int $newValue)
    {
        $currentOrder = 1;
        foreach ($otherEntities as $otherEntity) {
            if ($currentOrder == $newValue) {
                $currentOrder++; // Skip the position we want for our target entity
            }
            $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Reassigning priority order', context: ['entityId' => $otherEntity->getId(), 'newOrder' => $currentOrder]);
            $otherEntity->setPriorityOrder($currentOrder++);
        }
    }


    /**
     * Reorganizes the priority orders of other entities when the priority order of a specific entity is updated.
     *
     * This function checks the new priority order value against the original value and adjusts the priority orders of other entities accordingly.
     * If the new value is less than the original value, it increments the priority order of entities with a priority order between the new and original values.
     * If the new value is greater than the original value, it decrements the priority order of entities with a priority order between the original and new values.
     *
     * @param array $otherEntities An array of other entities to consider when reordering
     * @param int $newValue The new priority order value to assign to the entity
     * @param int $originalValue The original priority order of the entity
     *
     * @return void
     */
    private function  reorganizeSpecificPriorityOrder(array $otherEntities, int $newValue, int $originalValue)
    {
        if ($newValue < $originalValue) {
            foreach ($otherEntities as $otherEntity) {
                $otherPriorityOrder = $otherEntity->getPriorityOrder();
                if ($otherPriorityOrder >= $newValue && $otherPriorityOrder < $originalValue) {
                    $otherEntity->setPriorityOrder($otherPriorityOrder + 1);
                }
            }
        } else {
            foreach ($otherEntities as $otherEntity) {
                $otherPriorityOrder = $otherEntity->getPriorityOrder();
                if ($otherPriorityOrder <= $newValue && $otherPriorityOrder > $originalValue) {
                    $otherEntity->setPriorityOrder($otherPriorityOrder - 1);
                }
            }
        }
    }
}
