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
