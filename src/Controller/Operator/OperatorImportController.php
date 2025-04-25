<?php

namespace App\Controller\Operator;

use App\Controller\Operator\OperatorBaseController;

use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use App\Repository\UapRepository;
use App\Repository\TeamRepository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class OperatorImportController extends AbstractController
{

    public $em;
    public $request;
    public $logger;

    public $operatorBaseController;

    public $uapRepository;
    public $teamRepository;



    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,
        RequestStack                    $requestStack,
        OperatorBaseController          $operatorBaseController,

        UapRepository                   $uapRepository,
        TeamRepository                  $teamRepository,


    ) {

        $this->em                               = $em;
        $this->logger                           = $logger;
        $this->request                          = $requestStack->getCurrentRequest();

        $this->operatorBaseController           = $operatorBaseController;

        $this->uapRepository                    = $uapRepository;
        $this->teamRepository                   = $teamRepository;
    }







    #[Route('/operator/import', name: 'app_operator_import')]
    public function importOpe(Request $request, ValidatorInterface $validator, ManagerRegistry $doctrine)
    {
        /** @var EntityManagerInterface $em */
        $em = $doctrine->getManager();

        $unknownTeam = $this->teamRepository->findOneBy(['name' => 'INDEFINI']);
        $unknownUap = $this->uapRepository->findOneBy(['name' => 'INDEFINI']);
        if ($unknownTeam == null) {
            $unknownTeam = new Team();
            $unknownTeam->setName('INDEFINI');
            $em->persist($unknownTeam);
            $em->flush();
        }
        if ($unknownUap == null) {
            $unknownUap = new Uap();
            $unknownUap->setName('INDEFINI');
            $em->persist($unknownUap);
            $em->flush();
        }

        // Get all existing teams and UAPs
        $existingTeams = $this->teamRepository->findAll();
        $existingUaps = $this->uapRepository->findAll();

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
                return new Response('Failed to open the file.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            return new Response('No file uploaded or invalid file.', Response::HTTP_BAD_REQUEST);
        }

        // Begin a transaction
        $em->beginTransaction();
        try {
            // Process the data
            foreach ($ope_data as $data) {
                $code = $data[1];
                $firstname = $data[2];
                $surname = $data[3];
                $name = strtolower($firstname . '.' . $surname);

                // Find or default to 'INDEFINI' for team
                $team = $this->operatorBaseController->findEntityByName($existingTeams, $data[4], "INDEFINI");

                // Find or default to 'INDEFINI' for UAP
                $uap = $this->operatorBaseController->findEntityByName($existingUaps, $data[5], "INDEFINI");
                if ($uap->getName() === 'INDEFINI') {
                    $uap = $this->operatorBaseController->findEntityByName($existingUaps, $data[4], "INDEFINI");
                }

                $operator = new Operator();
                $operator->setCode($code);
                $operator->setName($name);
                $operator->setTeam($team);
                $operator->addUap($uap);

                // Validate the operator
                $errors = $validator->validate($operator);

                // Handle validation errors
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                    continue; // Skip this operator if there are validation errors
                }

                $suffix = 1;
                while (true) {
                    try {
                        $em->persist($operator);
                        $em->flush();

                        break; // Exit loop if successful
                    } catch (UniqueConstraintViolationException $e) {
                        // Modify the violating field and retry
                        $operator->setName($name . '_' . $suffix);
                        $suffix++;
                    }
                }
            }
            // Commit the transaction
            $em->commit();
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong
            $em->rollback();

            // Reset the EntityManager if it's closed
            if (!$em->isOpen()) {
                $em = $doctrine->resetManager();
            }
            $this->cache->delete('operators_list');

            // Re-throw the exception for further handling
            throw $e;
        }

        $this->addFlash('success', 'Les opérateurs ont bien été importés');
        return $this->redirectToRoute('app_operator');
    }
}
