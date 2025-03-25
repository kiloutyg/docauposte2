<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;

use App\Entity\Upload;
use App\Entity\TrainingRecord;
use App\Entity\Operator;
use App\Entity\Trainer;

use App\Repository\TrainingRecordRepository;
use App\Repository\OperatorRepository;
use App\Repository\UploadRepository;
use App\Repository\TrainerRepository;

use App\Service\EntityDeletionService;

use Symfony\Component\Validator\Constraints\DateTime;

class TrainingRecordService extends AbstractController
{
    protected $logger;
    protected $em;

    protected $trainingRecordRepository;
    protected $operatorRepository;
    protected $uploadRepository;
    protected $trainerRepository;

    private $entityDeletionService;


    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,

        TrainingRecordRepository        $trainingRecordRepository,
        OperatorRepository              $operatorRepository,
        UploadRepository                $uploadRepository,
        TrainerRepository               $trainerRepository,

        EntityDeletionService           $entityDeletionService

    ) {
        $this->logger                       = $logger;
        $this->em                           = $em;

        $this->trainingRecordRepository     = $trainingRecordRepository;
        $this->operatorRepository           = $operatorRepository;
        $this->uploadRepository             = $uploadRepository;
        $this->trainerRepository            = $trainerRepository;

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
        return $this->orderedTrainingRecordsLoop($operators, $trainingRecords);
    }


    public function getOrderedTrainingRecordsByTrainingRecordsArray($trainingRecords)
    {
        $operators = array_map(function ($record) {
            return $record->getOperator();
        }, $trainingRecords);
        usort($operators, [$this->trainingRecordRepository, 'compareOperator']);
        return $this->orderedTrainingRecordsLoop($operators, $trainingRecords);
    }

    private function orderedTrainingRecordsLoop(array $operators, array $trainingRecords)
    {
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


    public function trainingRecordtreatment(Request $request)
    {
        $this->logger->info('full request request in trainingRecordtreatment', [$request->request->all()]);
        $this->logger->info('full request attributes in trainingRecordtreatment', [$request->attributes->all()]);

        $operators = [];
        $operators = $request->request->all('operators');
        $upload = $this->uploadRepository->find($request->attributes->get('uploadId'));
        $trainerOperator = $this->operatorRepository->find($request->request->get('trainerId'));
        $trainerEntity = $this->trainerRepository->findOneBy(['operator' => $trainerOperator]);

        foreach ($operators as $operator) {
            if (array_key_exists("trained", $operator)) {

                $operatorEntity = $this->operatorRepository->find($operator['id']);
                $trained = ($operator['trained'] === '') ? null : (($operator['trained'] === 'true') ? true : false);

                if ($trained === null) {
                    break;
                }

                // Get all training records as a collection
                $operatorTrainingRecords = $operatorEntity->getTrainingRecords();
                // Filter the collection to find the record with the matching $upload
                $filteredRecords = $operatorTrainingRecords->filter(function ($trainingRecord) use ($upload) {
                    return $trainingRecord->getUpload() === $upload;
                });

                // Check if a TrainingRecord exists in the filtered collection
                if (!$filteredRecords->isEmpty()) {
                    $existingTrainingRecord = $filteredRecords->first();

                    // Make sure $existingTrainingRecord is indeed a TrainingRecord instance
                    if ($existingTrainingRecord instanceof TrainingRecord) {
                        $existingTrainingRecord->setTrained($trained);
                        $existingTrainingRecord->setTrainer($trainerEntity);
                        $existingTrainingRecord->setDate(new \DateTime());
                        $this->em->persist($existingTrainingRecord);
                        $operatorEntity->setLasttraining(new \DateTime());
                        $operatorEntity->setTobedeleted(null);
                        $operatorEntity->setInactiveSince(null);
                        $this->em->persist($operatorEntity);
                    }
                } else {
                    // If the collection was empty, create a new TrainingRecord
                    $trainingRecord = new TrainingRecord();
                    $trainingRecord->setOperator($operatorEntity);
                    $trainingRecord->setUpload($upload);
                    $trainingRecord->setDate(new \DateTime());
                    $trainingRecord->setTrained($trained);
                    $trainingRecord->setTrainer($trainerEntity);
                    $this->em->persist($trainingRecord);
                    $operatorEntity->setLasttraining(new \DateTime());
                    $operatorEntity->setTobedeleted(null);
                    $operatorEntity->setInactiveSince(null);
                    $this->em->persist($operatorEntity);
                }
            }
        }

        try {
            $this->trainerOperatorTrainingRecordCheck($trainerOperator, $trainerEntity, $upload);
        } catch (\Exception $e) {
            $this->logger->error('error during trainerOperatorTrainingRecordCheck', [$e]);
        } finally {
            $this->em->flush();
            return;
        }
    }



    public function trainerOperatorTrainingRecordCheck(Operator $trainerOperator, Trainer $trainerEntity, Upload $upload)
    {

        $existingTrainingRecord = $this->trainingRecordRepository->findOneBy(['upload' => $upload, 'operator' => $trainerOperator]);
        $this->logger->info('existingTrainingRecord', [$existingTrainingRecord]);
        if (!$existingTrainingRecord) {
            $this->logger->info('!$existingTrainingRecord', [true]);
            $existingTrainingRecord = new TrainingRecord();
            $existingTrainingRecord->setOperator($trainerOperator);
            $existingTrainingRecord->setTrainer($trainerEntity);
            $existingTrainingRecord->setUpload($upload);
        }

        $existingTrainingRecord->setDate(new \DateTime());
        $existingTrainingRecord->setTrained(true);
        $this->em->persist($existingTrainingRecord);

        $trainerOperator->setLasttraining(new \DateTime());
        $trainerOperator->setTobedeleted(null);
        $trainerOperator->setInactiveSince(null);
        $this->em->persist($trainerOperator);
        $this->logger->info('trainerOperator', [$trainerOperator]);

        return;
    }
}
