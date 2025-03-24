<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\ProductLineRepository;
use App\Repository\IncidentRepository;
use App\Repository\IncidentCategoryRepository;

use App\Entity\Incident;

use App\Service\NamingService;
use App\Service\FolderService;
use App\Service\FileTypeService;


// This class is responsible for the logic of managing the incidents files
class IncidentService extends AbstractController
{

    private $manager;
    private $productLineRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;

    private $namingService;
    private $folderService;
    private $fileTypeService;

    public function __construct(
        EntityManagerInterface $manager,

        IncidentRepository $incidentRepository,
        ProductLineRepository $productLineRepository,
        IncidentCategoryRepository $incidentCategoryRepository,

        NamingService $namingService,
        FolderService $folderService,
        FileTypeService $fileTypeService,
    ) {
        $this->manager = $manager;

        $this->productLineRepository = $productLineRepository;
        $this->incidentRepository = $incidentRepository;
        $this->incidentCategoryRepository = $incidentCategoryRepository;

        $this->namingService = $namingService;
        $this->folderService = $folderService;
        $this->fileTypeService = $fileTypeService;
    }


    // This function is responsible for the logic of uploading the incidents files
    public function uploadIncidentFiles(Request $request)
    {
        // Get the URL of the page from which the request originated
        $originUrl = $request->headers->get('referer');

        $files = $request->files->all();

        $incidentCategory = $this->incidentCategoryRepository->find($request->request->get('incident_incidentCategory'));

        $productLine = $this->productLineRepository->find($request->request->get('incident_productLine'));

        $autoDisplayPriority = $request->request->get('incident_autoDisplayPriority');

        foreach ($files as $file) {

            $this->fileTypeService->checkFileType($file);
            // Filename checks to see if compliant and if a newname has been chosen by user
            if (!$this->namingService->filenameChecks($request, $request->request->get('incident_newFileName'))) {
                return $this->redirect($originUrl);
            } else {
                $name = $this->namingService->filenameChecks($request, $request->request->get('incident_newFileName'));
            }

            // Dynamic folder creation in the case it does not aleady exist
            $folderPath = $this->folderService->pathFindingDoc($productLine->getName());
            $path = $folderPath . '/' . $name;
            $file->move($folderPath . '/', $name);

            $incident = new incident();
            $incident->setFile(new File($path));
            $incident->setName($name);
            $incident->setPath($path);
            $incident->setUploader($this->getUser());
            $incident->setIncidentCategory($incidentCategory);
            $incident->setProductLine($productLine);
            $incident->setuploadedAt(new \DateTime());
            $incident->setAutoDisplayPriority($autoDisplayPriority);
            $this->manager->persist($incident);
        }
        $this->manager->flush();
        return $name;
    }





    // This function is responsible for the logic of deleting the incidents files 
    public function deleteIncidentFile($incidentEntity)
    {
        $incidentName = $incidentEntity->getName();

        $path = $incidentEntity->getPath();

        if (file_exists($path)) {
            unlink($path);
        }

        $this->manager->remove($incidentEntity);
        $this->manager->flush();
        return $incidentName;
    }


    // This function is responsible for the logic of modifying the incidents files
    public function modifyIncidentFile(incident $incident)
    {
        // Dynamic folder creation in the case it does not aleady exist
        $folderPath = $this->folderService->pathFindingDoc($incident->getProductLine()->getName());

        // Get the new file directly from the incident object
        $newFile = $incident->getFile();
        // Check if the file is of the right type
        if ($newFile->getMimeType() != 'application/pdf') {
            throw new \Exception('Le fichier doit être un pdf');
        }

        // Move the new file to the directory
        try {
            $newFile->move($folderPath . '/', $incident->getName());
        } catch (\Exception $e) {
            throw $e;
        }

        // Update the uploader in the incident object
        $incident->setUploader($this->getUser());

        if ($incident->getAutoDisplayPriority() != 0) {
            $incident->setActivateAutoDisplay(true);
        } else {
            $incident->setActivateAutoDisplay(false);
        }

        // Persist changes and flush to the database
        $incident->setuploadedAt(new \DateTime());
        $this->manager->persist($incident);
        $this->manager->flush();

        $this->addFlash('success', 'Incident modifié');
        return $this->redirectToRoute('app_incident_modify_file', [
            'incidentId' => $incident->getId()
        ]);
    }




    // This function is responsible for the logic of grouping the incidents files by parent entity
    public function groupIncidents($incidents)
    {

        $groupedincidents = [];

        // Group incidents by zone, productLine, category, and productLine
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




    public function displayIncident(?int $productLineId = null, ?int $incidentId = null)
    {

        $incidentEntity = null;
        if ($incidentId != null) {
            $incidentEntity = $this->incidentRepository->find(['id' => $incidentId]);
        }

        // If the incident does not exist, we get the productLine entity from the productLine id
        if (!$incidentEntity) {
            $productLine = $this->productLineRepository->find($productLineId);
        } else {
            $productLine = $incidentEntity->getProductLine();
        }

        $incidentsInProductLine   = [];

        // Get all the incidents of the productLine and sort them by id ascending
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
