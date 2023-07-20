<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Repository\ProductLineRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;

use App\Entity\Incident;
use App\Entity\ProductLine;

use App\Service\FolderCreationService;


// This class is responsible for the logic of managing the incidents files
class IncidentsService extends AbstractController
{

    protected $incidentRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $productlineRepository;
    protected $folderCreationService;
    protected $incidentCategoryRepository;


    public function __construct(
        FolderCreationService $folderCreationService,
        ProductLineRepository $productlineRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        IncidentRepository $incidentRepository,
        LoggerInterface $logger,
        IncidentCategoryRepository $incidentCategoryRepository,
    ) {
        $this->incidentRepository = $incidentRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;
        $this->productlineRepository = $productlineRepository;
        $this->folderCreationService = $folderCreationService;
        $this->incidentCategoryRepository = $incidentCategoryRepository;
    }

    // This function is responsible for the logic of uploading the incidents files
    public function uploadIncidentFiles(Request $request, $productline,  $IncidentCategoryId, $newName = null)
    {
        $allowedExtensions = ['pdf'];
        $files = $request->files->all();
        $public_dir = $this->projectDir . '/public';
        $IncidentCategory = $this->incidentCategoryRepository->findoneBy(['id' => $IncidentCategoryId]);

        foreach ($files as $file) {

            // Dynamic folder creation in the case it does not aleady exist
            $productlinename = $productline->getName();
            $parts = explode('.', $productlinename);
            $parts = array_reverse($parts);
            $folderPath = $public_dir . '/doc';

            foreach ($parts as $part) {
                $folderPath .= '/' . $part;
            }

            // Check if the file is a pdf
            $extension = $file->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }
            if ($file->getMimeType() != 'application/pdf') {
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }

            // Check if the user added a new name for the file
            if ($newName) {
                $name   = $newName;
            } else {
                $name   = $file->getClientOriginalName();
            }

            $path       = $folderPath . '/' . $name;
            $file->move($folderPath . '/', $name);

            $name;

            $incident = new incident();
            $incident->setFile(new File($path));
            $incident->setName($name);
            $incident->setPath($path);
            $incident->setIncidentCategory($IncidentCategory);
            $incident->setProductLine($productline);
            $incident->setuploadedAt(new \DateTime());
            $this->manager->persist($incident);
        }
        $this->manager->flush();
        return $name;
    }

    // This function is responsible for the logic of deleting the incidents files 
    public function deleteIncidentFile($name, $productline)
    {
        $public_dir = $this->projectDir . '/public';

        // Dynamic folder creation and file incident
        $productlinename = $productline->getName();
        $parts = explode('.', $productlinename);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $path       = $folderPath . '/' . $name;

        if (file_exists($path)) {
            unlink($path);
        }

        $incident = $this->incidentRepository->findOneBy(['name' => $name, 'ProductLine' => $productline]);
        $this->manager->remove($incident);
        $this->manager->flush();
        return $name;
    }


    // This function is responsible for the logic of modifying the incidents files
    public function modifyIncidentFile(incident $incident)
    {

        // Get the new file directly from the incident object
        $newFile = $incident->getFile();

        // Public directory
        $public_dir = $this->projectDir . '/public';

        // Old file path
        $oldFilePath = $incident->getPath();

        // New file path
        // Dynamic folder creation and file incident
        $productlinename = $incident->getProductLine()->getName();
        $parts = explode('.', $productlinename);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $Path = $folderPath . '/' . $incident->getName();



        // If new file exists, process it and delete the old one
        if ($newFile) {
            // Check if the file is of the right type
            if ($newFile->getMimeType() != 'application/pdf') {
                throw new \Exception('Le fichier doit être un pdf');
            }

            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Move the new file to the directory
            try {
                $newFile->move($folderPath . '/', $incident->getName());
            } catch (\Exception $e) {
                throw $e;
            }

            // Update the file path in the incident object
            $incident->setPath($Path);
        } else {
            // If no new file is incidented, just rename the old one if necessary
            if ($oldFilePath != $Path) {
                rename($oldFilePath, $Path);
                $incident->setPath($Path);
            }
        }

        // Persist changes and flush to the database
        $incident->setuploadedAt(new \DateTime());
        $this->manager->persist($incident);
        $this->manager->flush();
    }


    // This function is responsible for the logic of grouping the incidents files by parent entity
    public function groupIncidents($incidents)
    {

        $groupedincidents = [];

        // Group incidents by zone, productLine, category, and productline
        foreach ($incidents as $incident) {
            $zoneName = $incident->getProductLine()->getZone()->getName();

            $productLineName = $incident->getProductLine()->getName();

            if (!isset($groupedincidents[$zoneName])) {
                $groupedincidents[$zoneName] = [];
            }

            if (!isset($groupedincidents[$zoneName][$productLineName])) {
                $groupedincidents[$zoneName][$productLineName] = [];
            }

            $groupedincidents[$zoneName][$productLineName][] = $incident;
        }

        return $groupedincidents;
    }
}