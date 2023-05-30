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

use App\Entity\Incident;
use App\Entity\ProductLine;

use App\Service\FolderCreationService;


class IncidentsService extends AbstractController
{

    protected $incidentRepository;
    protected $manager;
    protected $projectDir;
    protected $logger;
    protected $productlineRepository;
    protected $folderCreationService;


    public function __construct(
        FolderCreationService $folderCreationService,
        ProductLineRepository $productlineRepository,
        EntityManagerInterface $manager,
        ParameterBagInterface $params,
        IncidentRepository $incidentRepository,
        LoggerInterface $logger
    ) {
        $this->incidentRepository = $incidentRepository;
        $this->manager = $manager;
        $this->projectDir = $params->get('kernel.project_dir');
        $this->logger = $logger;
        $this->productlineRepository = $productlineRepository;
        $this->folderCreationService = $folderCreationService;
    }
    public function incidentFiles(Request $request, $button, $newFileName = null)
    {
        $allowedExtensions = ['pdf'];
        $files = $request->files->all();
        $public_dir = $this->projectDir . '/public';

        foreach ($files as $file) {


            // Dinamyic folder creation and file incident
            $buttonname = $button->getName();
            $parts = explode('.', $buttonname);
            $parts = array_reverse($parts);
            $folderPath = $public_dir . '/doc';

            foreach ($parts as $part) {
                $folderPath .= '/' . $part;
            }

            $extension = $file->guessExtension();
            if (!in_array($extension, $allowedExtensions)) {
                return $this->addFlash('error', 'Le fichier doit être un pdf');;
            }


            if ($newFileName) {
                $filename   = $newFileName;
            } else {
                $filename   = $file->getClientOriginalName();
            }

            // Add .pdf extension if it is missing
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'pdf') {
                $filename .= '.pdf';
            }

            $path       = $folderPath . '/' . $filename;
            $file->move($folderPath . '/', $filename);

            $name = $filename;

            $incident = new incident();
            $incident->setFile(new File($path));
            $incident->setFilename($filename);
            $incident->setPath($path);
            $incident->setButton($button);
            $incident->setincidentedAt(new \DateTime());
            $this->manager->persist($incident);
        }
        $this->manager->flush();
        return $name;
    }


    public function deleteFile($filename, $button)
    {
        $name = $filename;
        $public_dir = $this->projectDir . '/public';

        // Dinamyic folder creation and file incident
        $buttonname = $button->getName();
        $parts = explode('.', $buttonname);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $path       = $folderPath . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }

        $incident = $this->incidentRepository->findOneBy(['filename' => $filename, 'button' => $button]);
        $this->manager->remove($incident);
        $this->manager->flush();
        return $name;
    }



    public function modifyFile(incident $incident)
    {
        // Log the form data
        $this->logger->info('original incident state', ['incident' => $incident]);

        // Get the new file directly from the incident object
        $newFile = $incident->getFile();

        // Public directory
        $public_dir = $this->projectDir . '/public';


        // Old file path
        $oldFilePath = $incident->getPath();

        // New file path
        // Dynamic folder creation and file incident
        $buttonname = $incident->getProductLine()->getName();
        $parts = explode('.', $buttonname);
        $parts = array_reverse($parts);
        $folderPath = $public_dir . '/doc';

        foreach ($parts as $part) {
            $folderPath .= '/' . $part;
        }

        $Path = $folderPath . '/' . $incident->getFilename();

        // If new file exists, process it and delete the old one
        if ($newFile) {
            // Remove old file if it exists
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            // Move the new file to the directory
            try {
                $newFile->move($folderPath . '/', $incident->getFilename());
            } catch (\Exception $e) {
                $this->logger->error('Failed to move incidented file: ' . $e->getMessage());
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
        $incident->setincidentedAt(new \DateTime());
        $this->manager->persist($incident);
        $this->manager->flush();
    }


    public function groupincidents()
    {
        $incidents = $this->incidentRepository->findAll();

        $groupedincidents = [];

        // Group incidents by zone, productLine, category, and button
        foreach ($incidents as $incident) {
            $zoneName = $incident->getButton()->getCategory()->getProductLine()->getZone()->getName();
            $productLineName = $incident->getButton()->getCategory()->getProductLine()->getName();
            $categoryName = $incident->getButton()->getCategory()->getName();
            $buttonName = $incident->getButton()->getName();

            if (!isset($groupedincidents[$zoneName])) {
                $groupedincidents[$zoneName] = [];
            }

            if (!isset($groupedincidents[$zoneName][$productLineName])) {
                $groupedincidents[$zoneName][$productLineName] = [];
            }

            if (!isset($groupedincidents[$zoneName][$productLineName][$categoryName])) {
                $groupedincidents[$zoneName][$productLineName][$categoryName] = [];
            }

            if (!isset($groupedincidents[$zoneName][$productLineName][$categoryName][$buttonName])) {
                $groupedincidents[$zoneName][$productLineName][$categoryName][$buttonName] = [];
            }

            $groupedincidents[$zoneName][$productLineName][$categoryName][$buttonName][] = $incident;
        }

        return $groupedincidents;
    }
}