<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Upload;
use App\Entity\TrainingRecord;

use App\Repository\TrainingRecordRepository;

use App\Service\EntityDeletionService;

use Symfony\Component\Validator\Constraints\DateTime;

class TrainingRecordService extends AbstractController
{
    protected $logger;
    protected $em;

    protected $trainingRecordRepository;

    private $entityDeletionService;


    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,

        TrainingRecordRepository        $trainingRecordRepository,

        EntityDeletionService           $entityDeletionService

    ) {
        $this->logger                       = $logger;
        $this->em                           = $em;

        $this->trainingRecordRepository     = $trainingRecordRepository;

        $this->entityDeletionService        = $entityDeletionService;
    }

    public function updateTrainingRecord(Upload $upload)
    {
        // $this->logger->info('TrainingRecordService: updateTrainingRecord: upload: ' . $upload->getId());
        $trainingRecords = $upload->getTrainingRecords();
        $trained = false;
        if ($trainingRecords->isEmpty()) {
            // $this->logger->info('TrainingRecordService: updateTrainingRecord: trainingRecords is empty');
            return;
        } else {
            foreach ($trainingRecords as $trainingRecord) {
                // $this->logger->info('TrainingRecordService: updateTrainingRecord: trainingRecord: ' . $trainingRecord->getId());
                $trainingRecord->setTrained($trained);
                $this->em->persist($trainingRecord);
                $this->em->flush($trainingRecord);
            }
        }
        return;
    }

    public function getOrderedTrainingRecordsByUpload($upload)
    {
        $trainingRecords = $upload->getTrainingRecords()->toArray();

        $operators = array_map(function ($record) {
            return $record->getOperator();
        }, $trainingRecords);

        usort($operators, [$this->trainingRecordRepository, 'compareOperator']);

        $orderedTrainingRecords = [];
        foreach ($operators as $operator) {
            foreach ($trainingRecords as $record) {
                if ($record->getOperator() === $operator) {
                    $orderedTrainingRecords[] = $record;
                    break; // Assuming each operator is associated with a single record
                }
            }
        }


        return $orderedTrainingRecords;
    }


    public function getOrderedTrainingRecordsByTrainingRecordsArray($trainingRecords)
    {

        $operators = array_map(function ($record) {
            return $record->getOperator();
        }, $trainingRecords);

        usort($operators, [$this->trainingRecordRepository, 'compareOperator']);

        $orderedTrainingRecords = [];
        foreach ($operators as $operator) {
            foreach ($trainingRecords as $record) {
                if ($record->getOperator() === $operator) {
                    $orderedTrainingRecords[] = $record;
                    break; // Assuming each operator is associated with a single record
                }
            }
        }


        return $orderedTrainingRecords;
    }

    public function cheatTrain(string $date)
    {
        // $this->logger->info('TrainingRecordService: cheatTrain: date: ' . $date);

        $date = (new \DateTime($date));
        $trainingRecords = $this->trainingRecordRepository->findBy(['date' => $date]);
        // $this->logger->info('TrainingRecordService: cheatTrain: trainingRecords: ' . count($trainingRecords));
        foreach ($trainingRecords as $trainingRecord) {
            if ($trainingRecord->isTrained() === false) {
                $trainingRecord->setTrained(trained: true);
                $this->em->persist($trainingRecord);
                $this->em->flush($trainingRecord);
            }
        }
        return;
    }

    public function deleteWeeksOldTrainingRecords(int $trainingRecordId)
    {
        $trainingRecord = $this->trainingRecordRepository->find($trainingRecordId);

        // $this->logger->info('TrainingRecordService: deleteWeeksOldTrainingRecords', ['trainingRecordId' => $trainingRecord->getId()]);
        $today = new \DateTime();

        if ($trainingRecord->getDate() < $today->modify('-1 week')) {
            // $this->logger->info('TrainingRecordService: deleteWeeksOldTrainingRecords: trainingRecord is too old');
            return false;
        } else {
            $response = $this->entityDeletionService->deleteEntity('trainingRecord', $trainingRecord->getId());
        }

        if ($response != '' || $response != null) {
            // $this->logger->info('TrainingRecordService: deleteWeeksOldTrainingRecords: response: ' . $response);
            return true;
        } else {
            return false;
        }
    }
}
