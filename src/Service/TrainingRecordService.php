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
        $trainingRecords = $upload->getTrainingRecords();
        $trained = false;
        if (!$trainingRecords->isEmpty()) {
            foreach ($trainingRecords as $trainingRecord) {
                $trainingRecord->setTrained($trained);
                $this->em->persist($trainingRecord);
                $this->em->flush();
            }
        }
    }

    public function getOrderedTrainingRecordsByUpload($upload)
    {
        $trainingRecords = $upload->getTrainingRecords()->toArray();

        return $this->orderedTrainingRecordsLoop($trainingRecords);
    }



    public function getOrderedTrainingRecordsByTrainingRecordsArray($trainingRecords)
    {
        return $this->orderedTrainingRecordsLoop($trainingRecords);
    }



    private function orderedTrainingRecordsLoop(array $trainingRecords)
    {
        $operators = array_map(function ($trainingRecord) {
            return $trainingRecord->getOperator();
        }, $trainingRecords);
        usort($operators, [$this->trainingRecordRepository, 'compareOperator']);

        $orderedTrainingRecords = [];
        foreach ($operators as $operator) {
            foreach ($trainingRecords as $trainingRecord) {
                if ($trainingRecord->getOperator() === $operator) {
                    $orderedTrainingRecords[] = $trainingRecord;
                    break; // Assuming each operator is associated with a single record
                }
            }
        }
        return $orderedTrainingRecords;
    }

    public function deleteWeeksOldTrainingRecords(int $trainingRecordId)
    {
        $trainingRecord = $this->trainingRecordRepository->find($trainingRecordId);
        $today = new \DateTime();
        $response = false;

        if ($trainingRecord->getDate() < $today->modify('-1 week')) {
            return false;
        } else {
            $response = $this->entityDeletionService->deleteEntity('trainingRecord', $trainingRecord->getId());
        }
        if ($response != '' || $response != null) {
            return true;
        } else {
            return false;
        }
    }


    public function trainingRecordTreatment(Request $request)
    {
        $operators = [];
        $operators = $request->request->all('operators');
        $upload = $this->uploadRepository->find($request->attributes->get('uploadId'));
        $trainerOperator = $this->operatorRepository->find($request->request->get('trainerId'));
        $trainerEntity = $this->trainerRepository->findOneBy(['operator' => $trainerOperator]);

        foreach ($operators as $operator) {
            if (array_key_exists("trained", $operator)) {

                if ($operator['trained'] === 'true') {
                    $trained = true;
                } else {
                    continue;
                }

                $operatorEntity = $this->operatorRepository->find($operator['id']);
                // Get all training records as a collection
                $operatorTrainingRecords = $operatorEntity->getTrainingRecords();
                // Filter the collection to find the record with the matching $upload
                $filteredRecords = $operatorTrainingRecords->filter(function ($trainingRecord) use ($upload) {
                    return $trainingRecord->getUpload() === $upload;
                });

                // If the collection is empty, create a new TrainingRecord
                if ($filteredRecords->isEmpty()) {
                    $trainingRecord = new TrainingRecord();
                    $trainingRecord->setOperator($operatorEntity);
                    $trainingRecord->setUpload($upload);
                } else {
                    $trainingRecord = $filteredRecords->first();
                }
                $trainingRecord->setTrained($trained);
                $trainingRecord->setTrainer($trainerEntity);
                $trainingRecord->setDate(new \DateTime());
                $this->em->persist($trainingRecord);
                $operatorEntity->setLasttraining(new \DateTime());
                $operatorEntity->setTobedeleted(null);
                $operatorEntity->setInactiveSince(null);
                $this->em->persist($operatorEntity);
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
        if (!$existingTrainingRecord) {
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

        return;
    }
}
