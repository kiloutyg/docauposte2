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
use App\Entity\User;

use App\Service\FolderCreationService;
use Doctrine\Common\Collections\ArrayCollection;

// This class is responsible for the logic of managing the incidents files
class IncidentService extends AbstractController
{

    private $manager;
    private $projectDir;
    private $logger;

    private $productLineRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;

    private $folderCreationService;


    public function __construct(
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        LoggerInterface $logger,

        IncidentRepository $incidentRepository,
        ProductLineRepository $productLineRepository,
        FolderCreationService $folderCreationService,

        IncidentCategoryRepository $incidentCategoryRepository,
    ) {
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;

        $this->productLineRepository = $productLineRepository;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;

        $this->folderCreationService = $folderCreationService;
    }


    // This function is responsible for the logic of uploading the incidents files
    public function uploadIncidentFiles(Request $request, $productline,  $IncidentCategoryId, User $user, $newName = null)
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
            $originalName = pathinfo($name, PATHINFO_FILENAME); // Gets the filename without extension
            $fileExtension = pathinfo($name, PATHINFO_EXTENSION); // Gets the file extension

            if (file_exists($folderPath . '/' . $name)) {
                $iteration = count($this->incidentRepository->findBy(['name' => $name, 'ProductLine' => $productline]));
                $storageName = $originalName . '-' . ($iteration + 1) . '.' . $fileExtension;
                $path       = $folderPath . '/' . $storageName;
                $file->move($folderPath . '/', $storageName);
            } else {
                $path       = $folderPath . '/' . $name;
                $file->move($folderPath . '/', $name);
            }

            $incident = new incident();
            $incident->setFile(new File($path));
            $incident->setName($name);
            $incident->setPath($path);
            $incident->setUploader($user);
            $incident->setIncidentCategory($IncidentCategory);
            $incident->setProductLine($productline);
            $incident->setuploadedAt(new \DateTime());
            $this->manager->persist($incident);
        }
        $this->manager->flush();
        return $name;
    }

    // This function is responsible for the logic of deleting the incidents files 
    public function deleteIncidentFile($incidentEntity, $productlineEntity)
    {
        $incidentName = $incidentEntity->getName();

        $public_dir = $this->projectDir . '/public';

        // Dynamic folder creation and file incident
        $productlinename = $productlineEntity->getName();
        $parts = explode('.', $productlinename);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $path = $incidentEntity->getPath();

        if (file_exists($path)) {
            unlink($path);
        }

        $this->manager->remove($incidentEntity);
        $this->manager->flush();
        return $incidentName;
    }


    // This function is responsible for the logic of modifying the incidents files
    public function modifyIncidentFile(incident $incident, User $user)
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
            // Update the uploader in the incident object
            $incident->setUploader($user);
        } else {
            // If no new file is incidented, just rename the old one if necessary
            if ($oldFilePath != $Path) {
                rename($oldFilePath, $Path);
                $incident->setPath($Path);
                // Update the uploader in the incident object
                $incident->setUploader($user);
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
            $productLine = $incident->getProductLine();
            $productLineName = $productLine->getName();
            $zone = $productLine->getZone();
            $zoneName = $zone->getName();

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




    public function displayIncident(int $productLineId = null, int $incidentId = null)
    {
        
        $incidentEntity = null;
        if ($incidentId != null) {
            $incidentEntity = $this->incidentRepository->find(['id' => $incidentId]);
        }

        // If the incident does not exist, we get the productline entity from the productline id
        if (!$incidentEntity) {
            $productLine = $this->productLineRepository->find($productLineId);
        } else {
            $productLine = $incidentEntity->getProductLine();
        }

        $incidentsInProductLine   = [];

        // Get all the incidents of the productline and sort them by id ascending
        $incidentsInProductLine = $this->incidentRepository->findBy(
            ['productLine' => $productLineId],
            ['id' => 'ASC'] // order by id ascending
        );

        // Get the id of each incident and put them in an array
        $incidentIds = array_map(function ($incident) {
            return $incident->getId();
        }, $incidentsInProductLine);

        // Get the key of the current incident in the array
        $currentIncidentKey = array_search($incidentId, $incidentIds);

        // Get the current incident
        $incident = $incidentsInProductLine[$currentIncidentKey];

        // If the current incident does not exist, we set it to null
        if ($currentIncidentKey === false) {
            $incident = null;
        }

        // Get the next incident key in the array 
        $nextIncidentKey = $currentIncidentKey + 1;

        // Get the next incident in the array based on the next incident key
        $nextIncident  = isset($incidentsInProductLine[$nextIncidentKey]) ? $incidentsInProductLine[$nextIncidentKey] : null;

        return [$incident, $productLine, $nextIncident];
    }


}
