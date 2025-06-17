<?php

namespace App\Service;

use App\Entity\Operator;

use App\Service\EntityFetchingService;
use App\Service\OperatorService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OperatorImportService extends AbstractController
{
    private     $logger;

    private     $em;
    private $doctrine;
    private     $validator;

    private     $entityFetchingService;
    private     $operatorService;


    /**
     * Constructor for the OperatorImportService class.
     *
     * Initializes the service with necessary dependencies for logging, database operations,
     * validation, and related services for entity fetching and operator management.
     *
     * @param LoggerInterface $logger Logger service for recording operation information and errors
     * @param EntityManagerInterface $em Doctrine entity manager for database operations
     * @param ManagerRegistry $doctrine Doctrine manager registry for managing entity repositories
     * @param ValidatorInterface $validator Service for validating entity objects
     * @param EntityFetchingService $entityFetchingService Service for fetching related entities
     * @param OperatorService $operatorService Service for operator-specific operations
     */
    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,
        ManagerRegistry               $doctrine,
        ValidatorInterface              $validator,

        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService
    ) {
        $this->logger                   = $logger;
        $this->em                       = $em;
        $this->doctrine               = $doctrine;
        $this->validator                = $validator;

        $this->entityFetchingService    = $entityFetchingService;
        $this->operatorService          = $operatorService;
    }


    /**
     * Handles the import of operators from a CSV file.
     *
     * This method processes an uploaded CSV file containing operator data.
     * It extracts the data from the file, validates it, and passes it to the
     * processOpeData method for further processing and database insertion.
     *
     * @param Request $request The HTTP request containing the uploaded CSV file.
     *                         The file should be submitted with the name 'operator-import-file'.
     *
     * @return Response A Symfony Response object indicating the result of the import operation.
     *                  Returns HTTP 400 if no file is uploaded or the file is invalid.
     *                  Returns HTTP 500 if the file cannot be opened.
     *                  Returns the result of processOpeData() if the file is successfully processed.
     */
    public function importOpeService(Request $request): Response
    {
        // Handle the file upload
        $file = $request->files->get('operator-import-file');
        $ope_data = [];
        if ($file instanceof UploadedFile) {
            // Open the file
            if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                // Process the CSV data
                while (($data = fgetcsv($handle, 1000, ';', '"')) !== false) {
                    // Store $data in an array
                    $ope_data[] = $data;
                }
                // Close the file handle
                fclose($handle);
            } else {
                $this->logger->error('Failed to open the file.');
                return new Response('Failed to open the file.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $this->logger->error('No file uploaded.');
            return new Response('No file uploaded or invalid file.', Response::HTTP_BAD_REQUEST);
        }
        return $this->processOpeData($ope_data);
    }




    /**
     * Processes operator data extracted from a CSV file and persists valid operators to the database.
     *
     * This method handles the core logic of operator import by:
     * - Validating operator names against a regex pattern
     * - Checking for duplicate operators to avoid conflicts
     * - Associating operators with teams and UAPs
     * - Validating the created operator entities
     * - Persisting valid operators to the database within a transaction
     *
     * The entire operation is wrapped in a transaction to ensure data integrity.
     * If any critical error occurs, the transaction is rolled back.
     *
     * @param array $ope_data An array of arrays containing operator data from the CSV file.
     *                        Each inner array should contain operator details in the following order:
     *                        [0] => unknown/unused
     *                        [1] => operator code
     *                        [2] => operator first name
     *                        [3] => operator surname
     *                        [4] => team name
     *                        [5] => UAP name
     *
     * @return Response A Symfony Response object containing a summary of the import operation:
     *                  - Number of successfully imported operators
     *                  - Number of duplicates skipped
     *                  - Number of errors encountered
     *                  Returns HTTP 500 if a critical error occurs during processing.
     */
    private function processOpeData($ope_data): Response
    {
        $this->logger->info('OperatorImportService::processOpeData');
        $existingTeams = $this->entityFetchingService->getTeams();
        $existingUaps = $this->entityFetchingService->getUaps();

        $successCount = 0;
        $errorCount = 0;
        $duplicateCount = 0;

        // Start a transaction
        $this->em->getConnection()->beginTransaction();

        try {
            // Process the data
            foreach ($ope_data as $data) {

                $code = trim($data[1]);
                $firstname = trim($data[2]);
                $surname = trim($data[3]);
                $firstname = preg_replace('/[^a-zA-Z-]/', '', $firstname);
                $surname = preg_replace('/[^a-zA-Z-]/', '', $surname);
                $name = strtolower($firstname . '.' . $surname);

                // Add explicit regex check before even creating the entity
                $regexPattern = '/^(?!-)(?!.*--)[a-zA-Z-]+(?<!-)\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/';
                if (!preg_match($regexPattern, $name)) {
                    $this->addFlash('danger', 'Nom de l\'opérateur invalide: "' . $name . '"');
                    $this->logger->warning('Name does not match required pattern: "' . $name . '"');
                    $errorCount++;
                    continue;
                }

                // Check for existing operator - do this BEFORE creating a new entity
                $existingOperator = $this->doctrine->getRepository(Operator::class)->findOneBy(['name' => $name]);
                if (!$existingOperator) {
                    $existingOperator = $this->doctrine->getRepository(Operator::class)->findOneBy(['code' => $code]);
                }

                if ($existingOperator) {
                    $this->logger->warning('Duplicate operator skipped: ' . $name . ' (code: ' . $code . ')');
                    $duplicateCount++;
                    continue;
                }

                // Find or default to 'INDEFINI' for team and UAP
                $team = $this->operatorService->findEntityByName($existingTeams, $data[4], "INDEFINI");
                $uap = $this->operatorService->findEntityByName($existingUaps, $data[5], "INDEFINI");
                if ($uap->getName() === 'INDEFINI') {
                    $uap = $this->operatorService->findEntityByName($existingUaps, $data[4], "INDEFINI");
                }

                $operator = new Operator();
                $operator->setCode($code);
                $operator->setName($name);
                $operator->setTeam($team);
                $operator->addUap($uap);

                // Validate the operator
                $errors = $this->validator->validate(value: $operator, constraints: null, groups: ['Default', 'operator_details']);

                // Handle validation errors
                if (count($errors) > 0) {
                    $errorCount++;
                    $errorMessages = [];
                    foreach ($errors as $error) {
                        $errorMessages[] = $error->getMessage();
                        $this->addFlash('danger', 'Validation error for operator: ' . [$error->getMessage()]);
                        $this->logger->error('Validation error for operator: ' . $name, [$error->getMessage()]);
                    }

                    $this->logger->info(sprintf(
                        'Invalid data: code: %s, name: %s, team: %s, uap: %s, errors: %s',
                        $code,
                        $name,
                        $team->getName(),
                        $uap->getName(),
                        implode(', ', $errorMessages)
                    ));

                    // Skip this operator - do NOT persist
                    continue;
                }

                // Only persist valid operators
                $this->em->persist($operator);
                $successCount++;
            }

            // After processing all data, commit the transaction
            $this->em->flush();

            // If we got here without exceptions, commit the transaction
            $this->em->getConnection()->commit();
            $this->logger->info('Successfully committed all valid operators to the database');
        } catch (\Exception $e) {
            // Something went wrong, rollback the transaction
            $this->em->getConnection()->rollBack();
            $this->em->clear(); // Clear the entity manager

            $this->logger->error('Error processing operators, transaction rolled back: ' . $e->getMessage());
            return new Response(
                'Erreur lors de l\'importation des opérateurs: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new Response(
            sprintf(
                'Importation des opérateurs effectuée. %d opérateurs importés, %d doublons ignorés, %d erreurs.',
                $successCount,
                $duplicateCount,
                $errorCount
            ),
            Response::HTTP_OK
        );
    }
}
