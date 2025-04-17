<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Department;

use App\Repository\DepartmentRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;


class DepartmentService extends AbstractController
{
    private $departmentRepository;
    private $em;
    public function __construct(
        DepartmentRepository $departmentRepository,
        EntityManagerInterface $em
    ) {
        $this->departmentRepository = $departmentRepository;
        $this->em = $em;
    }

    public function departmentCreation(Request $request): array
    {
        $response = ['success' => true, 'message' => 'Le service a été créé'];

        // Get the data from the request
        $data = json_decode($request->getContent(), true);

        // Get the name of the department
        $departmentName = $data['department_name'] ?? null;

        // Get the existing department name
        $existingDepartment = $this->departmentRepository->findOneBy(['name' => $departmentName]);

        // Check if the department name is empty or if the department already exists
        if (empty($departmentName)) {
            $response = ['success' => false, 'message' => 'Le nom du service ne peut pas être vide'];
        }

        if ($existingDepartment) {
            $response = ['success' => false, 'message' => 'Ce service existe déjà'];
        } else {
            $department = new Department();
            $department->setName($departmentName);
            $this->em->persist($department);
            $this->em->flush();
        }
        return $response;
    }
}
