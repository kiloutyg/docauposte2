<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use App\Entity\Trainer;
use App\Entity\Operator;

use App\Service\SettingsService;

use App\Service\EntityFetchingService;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrainerService extends AbstractController
{
    private $em;
    private $logger;

    private $settingsService;
    private $entityFetchingService;
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
        SettingsService $settingsService,
        EntityFetchingService $entityFetchingService
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->settingsService = $settingsService;
        $this->entityFetchingService = $entityFetchingService;
    }



    /**
     * Handles trainer status updates for an operator.
     *
     * @param bool $isTrainer Indicates whether the operator should be promoted to trainer status.
     * @param Operator $operator The operator for which the trainer status needs to be updated.
     * @return void
     */
    public function handleTrainerStatus(bool $isTrainer, Operator $operator): void
    {
        $trainer = $operator->getTrainer();

        if ($isTrainer) {
            $this->promoteToTrainer($operator, $trainer);
        } else {
            $this->demoteFromTrainer($operator, $trainer);
        }
    }



    /**
     * Promote an operator to trainer status.
     *
     * This function ensures an operator has trainer status by either using an existing
     * trainer entity or creating a new one if none exists. It also marks the trainer
     * as not demoted and persists the changes to the database.
     *
     * @param Operator $operator The operator to be promoted to trainer status
     * @param Trainer|null $trainer The existing trainer entity associated with the operator, or null if none exists
     * @return void
     */
    private function promoteToTrainer(Operator $operator, ?Trainer $trainer): void
    {
        if ($trainer === null) {
            $trainer = new Trainer();
            $trainer->setOperator($operator);
            $operator->setTrainer($trainer);
        }

        $trainer->setDemoted(false);
        $this->em->persist($trainer);
    }




    /**
     * Demote an operator from trainer status.
     *
     * This function handles the demotion of an operator from trainer status. If the trainer
     * has associated training records, they are marked as demoted but retained in the system.
     * If no training records exist, the trainer entity is completely removed.
     *
     * @param Operator $operator The operator to be demoted from trainer status
     * @param Trainer|null $trainer The existing trainer entity associated with the operator, or null if none exists
     * @return void
     */
    private function demoteFromTrainer(Operator $operator, ?Trainer $trainer): void
    {
        if ($trainer === null) {
            return;
        }

        if (!$trainer->getTrainingRecords()->isEmpty()) {
            $trainer->setDemoted(true);
            $this->em->persist($trainer);
        } else {
            $operator->setIsTrainer(false);
            $this->em->remove($trainer);
        }
    }




    /**
     * Checks if a trainer has been active within the required retraining period.
     *
     * This function verifies if a trainer has conducted any training sessions within
     * the configured retraining delay period. If the trainer has recent activity,
     * their status is updated to active and their last training date is recorded.
     *
     * @param Operator $operator The operator with trainer status to check for activity
     * @return bool True if the trainer has been active within the retraining period, false otherwise
     */
    public function trainerInactivityCheck(Operator $operator): bool
    {
        $this->logger->info('Checking trainer activity for operator name : ' . $operator->getName());

        $operatorRetrainingDateInterval = $this->settingsService->getSettings()->getOperatorRetrainingDelay();
        $retrainingDelay = new \DateTime(datetime: 'now');
        $retrainingDelay->sub(interval: $operatorRetrainingDateInterval);

        $trainer = $operator->getTrainer();

        $trainingRecords = $this->entityFetchingService->findBy(
            entityType: 'trainingRecord',
            criteria: ['trainer' => $trainer],
            orderBy: ['date' => 'DESC']
        );

        if (empty($trainingRecords)) {
            $this->logger->info('No training records found for trainer name : ' . $operator->getName());
            return false;
        }

        if ($trainingRecords[0]->getDate() >= $retrainingDelay) {
            $this->logger->info('Trainer last training date : ' . $trainingRecords[0]->getDate()->format('Y-m-d H:i:s'));
            $operator->setInactiveSince(null);
            $operator->setTobedeleted(null);
            $operator->setLasttraining(new \DateTime());
            $this->em->persist($operator);
            return true;
        }
        $this->logger->info('trainerInactivityCheck return false ');
        
        return false;
    }
}
