<?php

namespace App\Service\Iluo;

use App\Repository\IluoLevelsRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

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




    public function updatePriorityOrders($otherEntities, $entity, $newValue)
    {
        $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Updating priority order', context: ['newValue' => $newValue]);

        $entityCount = count($otherEntities) + 1;
        $originalValue = $entity->getPriorityOrder();

        // Ensure newValue stays within valid bounds
        if ($newValue < 1) {
            $newValue = 1;
        }
        if ($newValue > $entityCount) {
            $newValue = $entityCount;
        }

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

        // If we found duplicates or invalid values, reassign all sort orders sequentially
        if ($needsReorganizing) {
            $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Reorganizing priority orders due to duplicates or invalid values');
            $currentOrder = 1;
            foreach ($otherEntities as $otherEntity) {
                if ($currentOrder == $newValue) {
                    $currentOrder++; // Skip the position we want for our target entity
                }
                $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Reassigning priority order', context: ['entityId' => $otherEntity->getId(), 'newOrder' => $currentOrder]);
                $otherEntity->setPriorityOrder($currentOrder++);
            }
        } else {
            // If no duplicates, proceed with normal reordering
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

        // Finally set the target entity's sort order
        $entity->setPriorityOrder($newValue);
        $this->logger->debug(message: 'iluoLevelsService::updatePriorityOrders - Priority order updated', context: ['entityId' => $entity->getId(), 'newValue' => $newValue]);
        $this->em->flush();
    }
}
