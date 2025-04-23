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
            throw new \Exception($errorsString);
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

        $uaps = $request->request->all('department_uaps');
        if ($uaps != []) {
            $this->logger->info('$uaps not empty');
            // Check if the array contains a '0' value, which means "no UAPs at all"
            if (in_array(0, $uaps)) {
                $this->logger->info('Value 0 found - removing all UAPs from department');
                // Remove all existing UAPs from the department
                $depUaps = $department->getUaps();
                foreach ($depUaps as $oldUap) {
                    $this->logger->info('Removing all UAPs - removing uap ' . $oldUap->getName());
                    $department->removeUap($oldUap);
                }
            } else {
                // Normal case - add selected UAPs and remove unselected ones
                foreach ($uaps as &$newUap) {
                    $uap = $this->uapRepository->find($newUap);
                    if ($uap) {
                        $this->logger->info('try to add uap ' . $uap->getName());
                        $department->addUap($uap);
                    } else {
                        $this->logger->warning('UAP with ID ' . $newUap . ' not found');
                    }
                }
                // Process removals - any UAP not in the submitted list should be removed
                $depUaps = $department->getUaps();
                foreach ($depUaps as $oldUap) {
                    $this->logger->info('try to remove uap ' . $oldUap->getName());
                    if (!in_array($oldUap->getId(), $uaps)) {
                        $this->logger->info('remove uap ' . $oldUap->getName());
                        $department->removeUap($oldUap);
                    }
                }
            }
        }

        $zones = $request->request->all('department_zones');
        if ($zones != []) {
            if (in_array(0, $zones)) {
                $this->logger->info('Value 0 found - removing all Zones from department');
                // Remove all existing Zones from the department
                $depZones = $department->getZones();
                foreach ($depZones as $oldZone) {
                    $this->logger->info('Removing all Zones - removing Zone ' . $oldZone->getName());
                    $department->removeZone($oldZone);
                }
            } else {
                // Normal case - add selected Zones and remove unselected ones
                $this->logger->info('$zones not empty');
                foreach ($zones as &$newZone) {
                    $zone = $this->zoneRepository->find($newZone);
                    $this->logger->info('try to add zone ' . $zone->getName());
                    $department->addZone($zone);
                }
                // Process removals - any Zone not in the submitted list should be removed
                $depZones = $department->getZones();
                foreach ($depZones as $oldZone) {
                    $this->logger->info('try to remove zone ' . $oldZone->getName());
                    if (in_array($oldZone->getId(), $zones) === false) {
                        $this->logger->info('remove zone ' . $oldZone->getName());
                        $department->removeZone($oldZone);
                    }
                }
            }
        }

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

        $this->em->persist($department);
        $this->em->flush();
        return $departmentName;
    }
}
