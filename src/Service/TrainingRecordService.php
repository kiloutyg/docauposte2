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


    /**
     * Constructor for the TrainingRecordService class.
     *
     * Initializes the service with necessary dependencies for managing training records.
     *
     * @param LoggerInterface $logger Logger for recording service operations
     * @param EntityManagerInterface $em Doctrine entity manager for database operations
     * @param TrainingRecordRepository $trainingRecordRepository Repository for training record entities
     * @param OperatorRepository $operatorRepository Repository for operator entities
     * @param UploadRepository $uploadRepository Repository for upload entities
     * @param TrainerRepository $trainerRepository Repository for trainer entities
     * @param EntityDeletionService $entityDeletionService Service for handling entity deletion operations
     */
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

    /**
     * Updates the training status of all training records associated with a specific upload.
     *
     * This method sets the 'trained' status to false for all training records linked to the provided upload.
     * Each record is persisted and the changes are flushed to the database.
     *
     * @param Upload $upload The upload entity whose associated training records need to be updated
     * @return void
     */
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

    /**
     * Retrieves training records associated with a specific upload and orders them.
     *
     * This method fetches all training records linked to the provided upload entity,
     * converts them to an array, and then passes them to the ordering function.
     *
     * @param Upload $upload The upload entity whose training records need to be ordered
     * @return array An ordered array of training records based on operator comparison
     */
    public function getOrderedTrainingRecordsByUpload($upload)
    {
        $this->logger->info('Ordering training records by upload');

        $trainingRecords = $upload->getTrainingRecords()->toArray();

        return $this->orderedTrainingRecordsLoop($trainingRecords);
    }



    /**
     * Orders training records from a provided array.
     *
     * This method takes an array of training records and passes it to the ordering function
     * to sort them based on operator comparison.
     *
     * @param array $trainingRecords An array of TrainingRecord entities to be ordered
     * @return array An ordered array of training records based on operator comparison
     */
    public function getOrderedTrainingRecordsByTrainingRecordsArray($trainingRecords)
    {
        return $this->orderedTrainingRecordsLoop($trainingRecords);
    }



    /**
     * Orders training records based on their associated operators.
     *
     * This method extracts operators from training records, sorts them using the repository's
     * compareOperator method, and then reorders the training records to match the operator order.
     * It ensures that training records are presented in a consistent order based on operator attributes.
     *
     * @param array $trainingRecords An array of TrainingRecord entities to be ordered
     * @return array An ordered array of training records based on operator comparison
     */
    private function orderedTrainingRecordsLoop(array $trainingRecords)
    {
        $this->logger->info(message: 'Ordering training records in TrainingRecordService::orderedTrainingRecordsLoop');
        $operators = array_map(callback: function ($trainingRecord): mixed {
            return $trainingRecord->getOperator();
        }, array: $trainingRecords);

        usort(array: $operators, callback: [$this->trainingRecordRepository, 'compareOperator']);

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

    /**
     * Deletes training records that are older than one week.
     *
     * This method checks if a training record is older than one week from the current date.
     * If it is not older than a week, it proceeds with deletion. Otherwise, it returns false
     * indicating that the record is too old to be deleted.
     *
     * @param int $trainingRecordId The ID of the training record to check and potentially delete
     * @return bool Returns true if the record was successfully deleted, false if the record is older than one week
     */
    public function deleteWeeksOldTrainingRecords(int $trainingRecordId)
    {
        $trainingRecord = $this->trainingRecordRepository->find($trainingRecordId);
        $today = new \DateTime();
        if ($trainingRecord->getDate() < $today->modify('-1 week')) {
            return false;
        } else {
            return $this->entityDeletionService->deleteEntity('trainingRecord', $trainingRecord->getId());
        }
          
    }


    /**
     * Processes training records based on request data.
     *
     * This method handles the creation or update of training records for operators based on
     * the submitted form data. It identifies trained operators, creates or updates their
     * training records, updates operator status information, and ensures the trainer is
     * also recorded as trained.
     *
     * @param Request $request The HTTP request containing training data:
     *                         - 'operators': Array of operator data with 'id' and 'trained' status
     *                         - 'trainerId': ID of the trainer conducting the training
     *                         - 'uploadId' (attribute): ID of the upload associated with the training
     *
     * @return void
     */
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
        }
    }



    /**
     * Ensures that a trainer is also recorded as trained for the upload they are training others on.
     *
     * This method checks if a training record exists for the trainer as an operator for the given upload.
     * If no record exists, it creates one. It then updates the training record with the current date
     * and marks the trainer as trained. Additionally, it updates the trainer's operator record
     * with the latest training date and clears any deletion or inactivity flags.
     *
     * @param Operator $trainerOperator The operator entity representing the trainer
     * @param Trainer $trainerEntity The trainer entity associated with the operator
     * @param Upload $upload The upload entity for which the training is being conducted
     * @return void
     */
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
    }
}
