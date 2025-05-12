<?php

namespace App\Service;

use App\Entity\Operator;

use App\Service\EntityFetchingService;
use App\Service\OperatorService;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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
     * @param ValidatorInterface $validator Service for validating entity objects
     * @param EntityFetchingService $entityFetchingService Service for fetching related entities
     * @param OperatorService $operatorService Service for operator-specific operations
     */

    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em,
        ValidatorInterface              $validator,
    
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService
    ) {
        $this->logger                   = $logger;
        $this->em                       = $em;
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
     * Processes operator data from a CSV import.
     *
     * This method processes the operator data extracted from a CSV file, creates new Operator entities,
     * associates them with the appropriate teams and UAPs, validates them, and persists them to the database.
     * If a name conflict occurs, it appends a numeric suffix to ensure uniqueness.
     *
     * @param array $ope_data An array of arrays containing operator data from the CSV file.
     *                        Expected format: [row][column] where columns are:
     *                        - [0]: Original DB ID
     *                        - [1]: Operator code
     *                        - [2]: Operator first name
     *                        - [3]: Operator surname
     *                        - [4]: Team name
     *                        - [5]: UAP name
     *
     * @return Response A Symfony Response object with a success message and HTTP 200 status code
     */
    private function processOpeData($ope_data): Response
    {
        $existingTeams = $this->entityFetchingService->getTeams();
        $existingUaps = $this->entityFetchingService->getUaps();
    
        // Process the data
        foreach ($ope_data as $data) {
            $code = $data[1];
            $firstname = $data[2];
            $surname = $data[3];
            $name = strtolower($firstname . '.' . $surname);
    
            // Find or default to 'INDEFINI' for team
            $team = $this->operatorService->findEntityByName($existingTeams, $data[4], "INDEFINI");
    
            // Find or default to 'INDEFINI' for UAP
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
            $errors = $this->validator->validate($operator);
    
            // Handle validation errors
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->logger->error('Validation error for operator: ' . $operator->getName(), [$error->getMessage()]);
                }
                continue; // Skip this operator if there are validation errors
            }
            $suffix = 1;
            while (true) {
                try {
                    $this->em->persist($operator);
                    $this->em->flush();
    
                    break; // Exit loop if successful
                } catch (UniqueConstraintViolationException $e) {
                    $operator->setName($name . '_' . $suffix);
                    $suffix++;
                }
            }
        }
        return new Response('Importation des opérateurs réussie.', Response::HTTP_OK);
    }
}
