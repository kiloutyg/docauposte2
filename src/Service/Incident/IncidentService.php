<?php

namespace App\Service\Incident;

use App\Entity\Incident;

use App\Service\NamingService;
use App\Service\FolderService;

use App\Service\Factory\RepositoryFactory;

use App\Service\Upload\FileTypeService;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\File\File;

use Psr\Log\LoggerInterface;


// This class is responsible for the logic of managing the incidents files
class IncidentService extends AbstractController
{

    private $logger;
    private $manager;

    private $repositoryFactory;

    private $productLineRepository;
    private $incidentRepository;
    private $incidentCategoryRepository;

    private $namingService;
    private $folderService;
    private $fileTypeService;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $manager,

        RepositoryFactory $repositoryFactory,

        NamingService $namingService,
        FolderService $folderService,
        FileTypeService $fileTypeService,
    ) {
        $this->logger = $logger;
        $this->manager = $manager;
        $this->repositoryFactory = $repositoryFactory;

        $this->productLineRepository = $this->repositoryFactory->getRepository('productLine');
        $this->incidentRepository = $this->repositoryFactory->getRepository('incident');
        $this->incidentCategoryRepository = $this->repositoryFactory->getRepository('incidentCategory');

        $this->namingService = $namingService;
        $this->folderService = $folderService;
        $this->fileTypeService = $fileTypeService;
    }


    // This function is responsible for the logic of uploading the incidents files
    /**
     * Handles the upload of incident files from a request.
     *
     * This function processes uploaded files, validates them, moves them to the appropriate
     * directory based on product line, and creates corresponding Incident entities in the database.
     *
     * @param Request $request The HTTP request containing the files and form data
     *                         Expected form fields:
     *                         - incident_incidentCategory: ID of the incident category
     *                         - incident_productLine: ID of the product line
     *                         - incident_newFileName: Optional new filename for the uploaded file
     *                         - incident_autoDisplayPriority: Priority for auto-display functionality
     *
     * @return string Returns the filename of the last processed file if successful,
     *                         or a redirect response if filename validation fails
     *
     * @throws \Exception If file type validation fails
     */
    public function uploadIncidentFiles(Request $request)
    {
        $files = $request->files->all();

        $incidentCategory = $this->incidentCategoryRepository->find($request->request->get('incident_incidentCategory'));

        $productLine = $this->productLineRepository->find($request->request->get('incident_productLine'));

        $autoDisplayPriority = $request->request->get('incident_autoDisplayPriority');

        foreach ($files as $file) {

            $this->fileTypeService->checkFileType($file);

            // Filename checks to see if compliant and if a newname has been chosen by user
            $name = $this->namingService->filenameChecks($request, $request->request->get('incident_newFileName'));

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
    /**
     * Deletes an incident file from the filesystem and removes its entity from the database.
     *
     * This function retrieves the file path from the incident entity, deletes the physical file
     * if it exists, and then removes the entity from the database.
     *
     * @param Incident $incidentEntity The incident entity to be deleted
     *
     * @return string Returns the name of the deleted incident file
     */
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



    /**
     * Updates an existing incident file with a new file.
     *
     * This function replaces the file associated with an incident entity. It validates
     * the file type, moves the new file to the appropriate directory, updates the incident
     * metadata (uploader, timestamp, auto-display settings), and persists the changes to the database.
     *
     * @param Incident $incident The incident entity to be modified, containing the new file
     *                           in its file property
     *
     * @return string Returns the name of the modified incident file
     *
     * @throws \InvalidArgumentException If the file is not a PDF
     * @throws \Exception If there is an error moving the file to its destination
     */
    public function modifyIncidentFile(incident $incident)
    {
        // Dynamic folder creation in the case it does not aleady exist
        $folderPath = $this->folderService->pathFindingDoc($incident->getProductLine()->getName());

        // Get the new file directly from the incident object
        $newFile = $incident->getFile();

        // Check if the file is of the right type
        if ($newFile->getMimeType() != 'application/pdf') {
            $this->logger->error('Invalid file type for incident file: ' . $incident->getName());
            throw new \InvalidArgumentException('Le fichier doit être un pdf');
        }

        // Move the new file to the directory
        try {
            $newFile->move($folderPath . '/', $incident->getName());
        } catch (\Exception $e) {
            $this->logger->error('Failed to move file: ' . $incident->getName(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Erreur lors du déplacement du fichier');
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

        return $incident->getName();
    }




    // This function is responsible for the logic of grouping the incidents files by parent entity
    /**
     * Groups incidents by their associated zones and product lines.
     *
     * This function organizes a collection of incident entities into a hierarchical array
     * structure, first grouped by zone name and then by product line name. This organization
     * facilitates displaying incidents in a structured manner based on their location and
     * product line association.
     *
     * @param array|iterable $incidents Collection of Incident entities to be grouped
     *
     * @return array Multidimensional array with the following structure:
     *               [zoneName => [productLineName => [incident1, incident2, ...], ...], ...]
     */
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




    /**
     * Retrieves an incident and related information for display purposes.
     *
     * This function fetches the specified incident, its associated product line,
     * and determines the next incident in sequence (if any) for navigation purposes.
     * If no specific incident is provided, it will use the product line to find relevant incidents.
     *
     * @param int|null $productLineId The ID of the product line to fetch incidents from
     * @param int|null $incidentId The ID of the specific incident to display
     *
     * @return array An array containing three elements:
     *               - The current incident entity (or null if not found)
     *               - The product line entity
     *               - The next incident entity in sequence (or null if there is no next incident)
     */
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
