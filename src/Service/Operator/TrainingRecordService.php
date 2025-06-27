<?php

namespace App\Service\Operator;

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
        $uploadTR = $upload->getTrainingRecords();

        $this->logger->info('getOrderedTrainingRecordsByUpload: uploadTR: ', [$uploadTR]);

        $trainingRecords = $uploadTR->toArray();

        $this->logger->info('getOrderedTrainingRecordsByUpload: trainingRecords: ', [$trainingRecords]);

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

        $this->logger->info('orderedTrainingRecordsLoop: operators: ', [$operators]);

        usort(array: $operators, callback: [$this->trainingRecordRepository, 'compareOperator']);

        $this->logger->info('orderedTrainingRecordsLoop after usort : operators: ', [$operators]);

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
        try {
            // Start transaction
            $this->em->beginTransaction();

            $operators = $request->request->all('operators');
            $upload = $this->uploadRepository->find($request->attributes->get('uploadId'));
            $trainerOperator = $this->operatorRepository->find($request->request->get('trainerId'));
            $trainerEntity = $this->trainerRepository->findOneBy(['operator' => $trainerOperator]);

            // Process regular operators
            $this->processOperatorTrainingRecords($operators, $upload, $trainerEntity, $trainerOperator);

            // Process the trainer's own record
            $this->processTrainerTrainingRecord($trainerOperator, $trainerEntity, $upload);

            // Commit all changes at once
            $this->em->flush();
            $this->em->commit();

            $this->logger->info('Successfully processed training records for upload ID: ' . $upload->getId());
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->em->rollback();
            $this->logger->warning('Attempted to create duplicate training record', [
                'error' => $e->getMessage(),
                'uploadId' => $request->attributes->get('uploadId')
            ]);
        } catch (\Exception $e) {
            $this->em->rollback();
            $this->logger->error('Error during trainingRecordTreatment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Process training records for regular operators.
     *
     * @param array $operators Array of operator data
     * @param Upload $upload The upload entity
     * @param Trainer $trainerEntity The trainer entity
     * @return void
     */
    private function processOperatorTrainingRecords(array $operators, Upload $upload, Trainer $trainerEntity, Operator $trainerOperator): void
    {
        foreach ($operators as $operator) {

            if (!array_key_exists("trained", $operator) || $operator['trained'] !== 'true') {
                continue;
            }

            $operatorEntity = $this->operatorRepository->find($operator['id']);

            if (!$operatorEntity) {
                $this->logger->warning('Operator not found', ['id' => $operator['id']]);
                continue;
            }

            if ($operatorEntity === $trainerOperator) {
                $this->logger->info('Trainer is trying to train himself', ['operatorId' => $operatorEntity->getId()]);
                continue;
            }

            // Get all training records as a collection
            $operatorTrainingRecords = $operatorEntity->getTrainingRecords();
            $this->logger->info('processOperatorTrainingRecords :: Operator name ', [$operatorEntity->getName()]);
            // Filter the collection to find the record with the matching $upload
            $filteredRecords = $operatorTrainingRecords->filter(function ($trainingRecord) use ($upload) {
                return $trainingRecord->getUpload()->getId() === $upload->getId();
            });
            $this->logger->info('processOperatorTrainingRecords :: filteredRecords: ', [$filteredRecords]);
            // If the collection is empty, create a new TrainingRecord
            if ($filteredRecords->isEmpty()) {
                $trainingRecord = new TrainingRecord();
                $trainingRecord->setOperator($operatorEntity);
                $trainingRecord->setUpload($upload);
            } else {
                $trainingRecord = $filteredRecords->first();
            }

            $trainingRecord->setTrained(true);
            $trainingRecord->setTrainer($trainerEntity);
            $trainingRecord->setDate(new \DateTime());
            $this->em->persist($trainingRecord);

            // Update operator status
            $operatorEntity->setLasttraining(new \DateTime());
            $operatorEntity->setTobedeleted(null);
            $operatorEntity->setInactiveSince(null);
            $this->em->persist($operatorEntity);
        }
    }

    /**
     * Process training record for the trainer.
     *
     * @param Operator $trainerOperator The operator entity representing the trainer
     * @param Trainer $trainerEntity The trainer entity
     * @param Upload $upload The upload entity
     * @return void
     */
    private function processTrainerTrainingRecord(Operator $trainerOperator, Trainer $trainerEntity, Upload $upload): void
    {
        $this->logger->info('processTrainerTrainingRecord :: trainer name ', [$trainerOperator->getName()]);

        // Check if the trainer already has a training record for this upload
        $operatorTrainingRecords = $trainerOperator->getTrainingRecords();
        $filteredRecords = $operatorTrainingRecords->filter(function ($trainingRecord) use ($upload) {
            return $trainingRecord->getUpload()->getId() === $upload->getId();
        });

        $this->logger->info('processTrainerTrainingRecord :: filteredRecords: ', [$filteredRecords]);

        if ($filteredRecords->isEmpty()) {
            $trainingRecord = new TrainingRecord();
            $trainingRecord->setOperator($trainerOperator);
            $trainingRecord->setUpload($upload);
            $trainingRecord->setTrainer($trainerEntity);
        } else {
            $trainingRecord = $filteredRecords->first();
        }

        $trainingRecord->setDate(new \DateTime());
        $trainingRecord->setTrained(true);
        $this->em->persist($trainingRecord);

        // Update trainer operator status
        $trainerOperator->setLasttraining(new \DateTime());
        $trainerOperator->setTobedeleted(null);
        $trainerOperator->setInactiveSince(null);
        $this->em->persist($trainerOperator);
    }


    public function cheatTrain(int $upload)
    {
        $uploadEntity = $this->uploadRepository->find($upload);
        $this->logger->info('TrainingRecordService: cheatTrain: uploadEntity: ', [$uploadEntity]);

        $uploadEntity = $this->uploadRepository->find($upload);
        $trainingRecords = $this->trainingRecordRepository->findBy(['upload' => $uploadEntity]);
        $this->logger->info('TrainingRecordService: cheatTrain: trainingRecords: ', [count($trainingRecords)]);
        foreach ($trainingRecords as $trainingRecord) {
            if ($trainingRecord->isTrained() === true) {
                $trainingRecord->setTrained(false);
                $this->em->persist($trainingRecord);
                $this->em->flush();
            }
        }
    }


    /**
     * Compares the upload date with the latest training record date.
     *
     * This method determines if an upload is newer than the most recent training record
     * associated with it. This is useful for identifying uploads that require new training
     * because their content has been updated since the last training session.
     *
     * @param Upload $upload The upload entity to check against training records
     * @return bool Returns true if the upload date is more recent than the latest training date,
     *              indicating that new training is required; false otherwise
     */
    public function lastTrainingDateUploadDateComparison(Upload $upload)
    {
        $response = false;
        $this->logger->info('TrainingRecordService: lastTrainingDateUploadDateComparison: upload: ', [$upload]);
        $lastTrainingRecord = $this->trainingRecordRepository->getLatestTrainingRecord($upload);
        $this->logger->info('TrainingRecordService: lastTrainingDateUploadDateComparison: lastTrainingRecord: ', [$lastTrainingRecord]);
        $uploadDate = $upload->getUploadedAt();
        $this->logger->info('TrainingRecordService: lastTrainingDateUploadDateComparison: uploadDate: ', [$uploadDate]);
        if ($uploadDate > $lastTrainingRecord->getDate()) {
            $response = true;
        }
        $this->logger->info('TrainingRecordService: lastTrainingDateUploadDateComparison: response: ', [$response]);
        return $response;
    }
}
