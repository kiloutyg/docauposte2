<?php

namespace App\Service;

use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Repository\UapRepository;
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

    public function __construct(
        DepartmentRepository                $departmentRepository,
        EntityManagerInterface              $em,
        LoggerInterface                     $logger,
        ValidatorInterface                  $validator,
        UapRepository                       $uapRepository
    ) {
        $this->departmentRepository         = $departmentRepository;
        $this->em                           = $em;
        $this->logger                       = $logger;
        $this->validator                    = $validator;
        $this->uapRepository                = $uapRepository;
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
        $depId = $request->request->get('department_id');
        // Check if department_id is provided
        if (!$depId) {
            $this->logger->error('No department ID provided');
            throw new \InvalidArgumentException('Department ID is missing');
        }

        $department = $this->departmentRepository->find($depId);
        if (!$department) {
            $this->logger->error('Department with ID' . $depId . 'not found ');
            throw new \InvalidArgumentException(sprintf('Department with ID %s not found', $depId));
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
            foreach ($uaps as &$newUap) {
                $uap = $this->uapRepository->find($newUap);
                $this->logger->info('try to add uap ' . $uap->getName());
                $department->addUap($uap);
            }
            $depUaps = $department->getUaps();
            foreach ($depUaps as $oldUap) {
                $this->logger->info('try to remove uap ' . $oldUap->getName());
                if (in_array($oldUap->getId(), $uaps) === false) {
                    $this->logger->info('remove uap ' . $oldUap->getName());
                    $department->removeUap($oldUap);
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
