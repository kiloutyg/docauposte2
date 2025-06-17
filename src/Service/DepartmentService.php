<?php

namespace App\Service;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Repository\UapRepository;
use App\Repository\ZoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DepartmentService extends AbstractController
{
    private $departmentRepository;
    private $em;
    private $logger;
    private $validator;
    private $uapRepository;
    private $zoneRepository;

    public function __construct(
        DepartmentRepository                $departmentRepository,
        EntityManagerInterface              $em,
        LoggerInterface                     $logger,
        ValidatorInterface                  $validator,
        UapRepository                       $uapRepository,
        ZoneRepository                      $zoneRepository
    ) {
        $this->departmentRepository         = $departmentRepository;
        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->validator                    = $validator;
        $this->uapRepository                = $uapRepository;
        $this->zoneRepository               = $zoneRepository;
    }

    /**
     * Creates a new department in the system.
     *
     * This method extracts the department name from the request,
     * creates a new Department entity, validates it, and persists
     * it to the database.
     *
     * @param Request $request The HTTP request containing the department_name parameter
     * @return string The name of the created department
     * @throws \InvalidArgumentException If the department_name is not provided or if validation fails
     */
    public function departmentCreation(Request $request): string
    {
        // Get the name of the department
        $departmentName = $request->request->get('department_name') ?? throw new \InvalidArgumentException();
        $department = new Department();
        $department->setName($departmentName);

        $errors = $this->validator->validate($department);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            $errorsString = implode("\n", $errorMessages);
            throw new \InvalidArgumentException($errorsString);
        }

        $this->em->persist($department);
        $this->em->flush();
        return $departmentName;
    }

    /**
     * Modifies an existing department in the system.
     *
     * This method updates a department's information based on the request data.
     * It can modify the department name and manage its associated UAPs and zones.
     * The department is validated before being persisted to the database.
     *
     * @param Request $request The HTTP request containing department modification data:
     *                         - department_id: ID of the department to modify
     *                         - department_name_mod: New name for the department (optional)
     *                         - department_uaps: Array of UAP IDs to associate with the department (optional)
     *                         - department_zones: Array of zone IDs to associate with the department (optional)
     * @return string The name of the modified department
     * @throws \InvalidArgumentException If the department_id is not provided or if the department is not found
     * @throws \Exception If validation of the modified department fails
     */
    public function departmentModification(Request $request): string
    {
        $departmentId = $request->request->get('department_id');
        // Check if department_id is provided
        if (!$departmentId) {
            $this->logger->error('No department ID provided');
            throw new \InvalidArgumentException('Department ID is missing');
        }
    
        $department = $this->departmentRepository->find($departmentId);
        if (!$department) {
            $this->logger->error('Department with ID' . $departmentId . 'not found ');
            throw new \InvalidArgumentException(sprintf('Department with ID %s not found', $departmentId));
        }
    
        $departmentNameNew = $request->request->get('department_name_mod');
        if ($departmentNameNew != '') {
            $department->setName($departmentNameNew);
        } else {
            $departmentName = $department->getName();
        }
    
        $this->departmentUapManagement($department, $request);
    
        $this->departmentZoneManagement($department, $request);
        $this->validatingDepartment($department);
    
        $this->em->persist($department);
        $this->em->flush();
        return $departmentName;
    }

    /**
     * Manages the UAPs associated with a department based on request data.
     *
     * This method processes the UAP associations for a department. It handles three scenarios:
     * 1. If no UAPs are provided in the request, it does nothing
     * 2. If the value 0 is included in the UAPs array, it removes all UAPs from the department
     * 3. Otherwise, it updates the department's UAPs based on the provided list
     *
     * @param Department $department The department entity to update
     * @param Request $request The HTTP request containing department_uaps parameter with UAP IDs
     * @return void
     */
    public function departmentUapManagement(Department $department, Request $request)
    {
        $uaps = $request->request->all('department_uaps');
        if (empty($uaps)) {
            return;
        }

        $this->logger->info('$uaps not empty');

        if (in_array(0, $uaps)) {
            $this->removeAllUapsFromDepartment($department);
            return;
        }

        $this->updateDepartmentUaps($department, $uaps);
    }

    /**
     * Removes all UAPs (Unités d'Affaires de Production) from a department.
     *
     * This method iterates through all UAPs currently associated with the department
     * and removes each one. It logs the removal process for each UAP.
     *
     * @param Department $department The department entity from which to remove all UAPs
     * @return void
     */
    private function removeAllUapsFromDepartment(Department $department): void
    {
        $this->logger->info('Value 0 found - removing all UAPs from department');
        $depUaps = $department->getUaps();
        foreach ($depUaps as $oldUap) {
            $this->logger->info('Removing all UAPs - removing uap ' . $oldUap->getName());
            $department->removeUap($oldUap);
        }
    }


    
    /**
     * Updates the UAPs associated with a department based on the provided list of UAP IDs.
     *
     * This method handles both adding new UAPs to the department and removing UAPs
     * that are no longer associated with the department. It first adds all selected UAPs
     * and then removes any existing UAPs that are not in the provided list.
     *
     * @param Department $department The department entity to update
     * @param array $uapIds Array of UAP IDs to associate with the department
     * @return void
     */
    private function updateDepartmentUaps(Department $department, array $uapIds): void
    {
        // Add new UAPs
        $this->addSelectedUaps($department, $uapIds);

        // Remove unselected UAPs
        $this->removeUnselectedUaps($department, $uapIds);
    }




    /**
     * Adds selected UAPs to a department based on their IDs.
     *
     * This method iterates through the provided UAP IDs, finds each UAP entity,
     * and associates it with the given department. It logs the addition process
     * and warns about any UAP IDs that cannot be found in the repository.
     *
     * @param Department $department The department entity to which UAPs will be added
     * @param array $uapIds Array of UAP IDs to be associated with the department
     * @return void
     */
    private function addSelectedUaps(Department $department, array $uapIds): void
    {
        foreach ($uapIds as $uapId) {
            $uap = $this->uapRepository->find($uapId);
            if ($uap) {
                $this->logger->info('try to add uap ' . $uap->getName());
                $department->addUap($uap);
            } else {
                $this->logger->warning('UAP with ID ' . $uapId . ' not found');
            }
        }
    }


    

    /**
     * Removes UAPs from a department that are not in the provided list of UAP IDs.
     *
     * This method iterates through all UAPs currently associated with the department
     * and removes any UAP whose ID is not included in the provided list of UAP IDs.
     * It logs each removal operation for tracking purposes.
     *
     * @param Department $department The department entity from which to remove unselected UAPs
     * @param array $uapIds Array of UAP IDs that should remain associated with the department
     * @return void
     */
    private function removeUnselectedUaps(Department $department, array $uapIds): void
    {
        $depUaps = $department->getUaps();
        foreach ($depUaps as $oldUap) {
            if (!in_array($oldUap->getId(), $uapIds)) {
                $this->logger->info('remove uap ' . $oldUap->getName());
                $department->removeUap($oldUap);
            }
        }
    }

    /**
     * Manages the zones associated with a department based on request data.
     *
     * This method processes the zone associations for a department. It handles three scenarios:
     * 1. If no zones are provided in the request, it does nothing
     * 2. If the value 0 is included in the zones array, it removes all zones from the department
     * 3. Otherwise, it updates the department's zones based on the provided list
     *
     * @param Department $department The department entity to update
     * @param Request $request The HTTP request containing department_zones parameter with zone IDs
     * @return void
     */
    public function departmentZoneManagement(Department $department, Request $request)
    {
        $zones = $request->request->all('department_zones');
        if (empty($zones)) {
            return;
        }

        if (in_array(0, $zones)) {
            $this->removeAllZonesFromDepartment($department);
            return;
        }

        $this->updateDepartmentZones($department, $zones);
    }


    
    /**
     * Removes all zones from a department.
     *
     * This method iterates through all zones currently associated with the department
     * and removes each one. It logs the removal process for each zone.
     *
     * @param Department $department The department entity from which to remove all zones
     * @return void
     */
    private function removeAllZonesFromDepartment(Department $department): void
    {
        $this->logger->info('Value 0 found - removing all Zones from department');
        $depZones = $department->getZones();
        foreach ($depZones as $oldZone) {
            $this->logger->info('Removing all Zones - removing Zone ' . $oldZone->getName());
            $department->removeZone($oldZone);
        }
    }


    
    /**
     * Updates the zones associated with a department based on the provided list of zone IDs.
     *
     * This method handles both adding new zones to the department and removing zones
     * that are no longer associated with the department. It first adds all selected zones
     * and then removes any existing zones that are not in the provided list.
     *
     * @param Department $department The department entity to update
     * @param array $zoneIds Array of zone IDs to associate with the department
     * @return void
     */
    private function updateDepartmentZones(Department $department, array $zoneIds): void
    {
        // Add new zones
        $this->addSelectedZones($department, $zoneIds);

        // Remove unselected zones
        $this->removeUnselectedZones($department, $zoneIds);
    }


    /**
     * Adds selected zones to a department based on their IDs.
     *
     * This method iterates through the provided zone IDs, finds each zone entity,
     * and associates it with the given department. It logs the addition process
     * and warns about any zone IDs that cannot be found in the repository.
     *
     * @param Department $department The department entity to which zones will be added
     * @param array $zoneIds Array of zone IDs to be associated with the department
     * @return void
     */
    private function addSelectedZones(Department $department, array $zoneIds): void
    {
        $this->logger->info('$zones not empty');
        foreach ($zoneIds as $zoneId) {
            $zone = $this->zoneRepository->find($zoneId);
            if ($zone) {
                $this->logger->info('try to add zone ' . $zone->getName());
                $department->addZone($zone);
            } else {
                $this->logger->warning('Zone with ID ' . $zoneId . ' not found');
            }
        }
    }


    
    /**
     * Removes zones from a department that are not in the provided list of zone IDs.
     *
     * This method iterates through all zones currently associated with the department
     * and removes any zone whose ID is not included in the provided list of zone IDs.
     * It logs each removal operation for tracking purposes.
     *
     * @param Department $department The department entity from which to remove unselected zones
     * @param array $zoneIds Array of zone IDs that should remain associated with the department
     * @return void
     */
    private function removeUnselectedZones(Department $department, array $zoneIds): void
    {
        $depZones = $department->getZones();
        foreach ($depZones as $oldZone) {
            if (!in_array($oldZone->getId(), $zoneIds)) {
                $this->logger->info('remove zone ' . $oldZone->getName());
                $department->removeZone($oldZone);
            }
        }
    }

    /**
     * Validates a department entity against defined validation constraints.
     *
     * This method uses the validator service to check if the department entity
     * meets all validation requirements. If validation fails, it collects all
     * error messages and throws an exception with the combined error messages.
     *
     * @param Department $department The department entity to validate
     * @return void
     * @throws \Exception If validation fails, with error messages as the exception message
     */
    public function validatingDepartment(Department $department)
    {
        $errors = $this->validator->validate($department);
        $this->logger->info('count errors: ' . count($errors));
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $violation) {
                $errorMessages[] = $violation->getMessage();
            }
            $errorsString = implode("\n", $errorMessages);
            throw new \Exception($errorsString);
        }
    }
}
