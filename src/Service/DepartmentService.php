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
     * Remove all UAPs from a department
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
     * Update department UAPs based on the provided list
     */
    private function updateDepartmentUaps(Department $department, array $uapIds): void
    {
        // Add new UAPs
        $this->addSelectedUaps($department, $uapIds);

        // Remove unselected UAPs
        $this->removeUnselectedUaps($department, $uapIds);
    }

    /**
     * Add selected UAPs to department
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
     * Remove UAPs that are not in the selected list
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
     * Remove all zones from a department
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
     * Update department zones based on the provided list
     */
    private function updateDepartmentZones(Department $department, array $zoneIds): void
    {
        // Add new zones
        $this->addSelectedZones($department, $zoneIds);

        // Remove unselected zones
        $this->removeUnselectedZones($department, $zoneIds);
    }

    /**
     * Add selected zones to department
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
     * Remove zones that are not in the selected list
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
