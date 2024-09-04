<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Upload;

use App\Repository\TrainingRecordRepository;

class TrainingRecordService extends AbstractController
{
    protected $logger;
    protected $em;

    protected $trainingRecordRepository;


    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,

        TrainingRecordRepository        $trainingRecordRepository

    ) {
        $this->logger                = $logger;
        $this->em                    = $em;

        $this->trainingRecordRepository = $trainingRecordRepository;
    }

    public function updateTrainingRecord(Upload $upload)
    {
        $this->logger->info('TrainingRecordService: updateTrainingRecord: upload: ' . $upload->getId());
        $trainingRecords = $upload->getTrainingRecords();
        $trained = false;
        if ($trainingRecords->isEmpty()) {
            $this->logger->info('TrainingRecordService: updateTrainingRecord: trainingRecords is empty');
            return;
        } else {
            foreach ($trainingRecords as $trainingRecord) {
                $this->logger->info('TrainingRecordService: updateTrainingRecord: trainingRecord: ' . $trainingRecord->getId());
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
}