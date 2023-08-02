<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;


use App\Repository\UploadRepository;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Repository\ApprobationRepository;

use App\Entity\Upload;
use App\Entity\Department;
use App\Entity\User;
use App\Entity\Validation;
use App\Entity\Approbation;


class ValidationService extends AbstractController

{
    protected $logger;
    protected $em;
    protected $uploadRepository;
    protected $departmentRepository;
    protected $userRepository;
    protected $validationRepository;
    protected $approbationRepository;

    public function __construct(
        LoggerInterface         $logger,
        EntityManagerInterface  $em,
        UploadRepository        $uploadRepository,
        DepartmentRepository    $departmentRepository,
        UserRepository          $userRepository,
        ValidationRepository    $validationRepository,
        ApprobationRepository   $approbationRepository
    ) {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->uploadRepository     = $uploadRepository;
        $this->departmentRepository = $departmentRepository;
        $this->userRepository       = $userRepository;
        $this->validationRepository = $validationRepository;
        $this->approbationRepository = $approbationRepository;
    }

    public function createValidation(Upload $upload, Request $request)
    {

        $validator_department_values = [];
        $validator_user_values = [];

        foreach ($request->request->keys() as $key) {
            if (strpos($key, 'validator_department') !== false) {
                $validator_department_values[] = $request->request->get($key);
            } elseif (strpos($key, 'validator_user') !== false) {
                $validator_user_values[] = $request->request->get($key);
            }
        }

        $validation = new Validation();

        $validation->setUpload($upload);

        $validation->setStatus(false);

        $this->em->persist($validation);

        $this->em->flush();

        $validator_user = null;
        $validator_department = null;

        foreach ($validator_department_values as $validator_department_value) {
            $validator_department = $this->departmentRepository->find($validator_department_value);
            $validation->addDepartment($validator_department);
            $this->createApprobationProcess($validation, $validator_user, $validator_department);
        }

        $validator_user = null;
        $validator_department = null;

        foreach ($validator_user_values as $validator_user_value) {
            $validator_user = $this->userRepository->find($validator_user_value);
            $validation->addValidator($validator_user);
            $this->createApprobationProcess($validation, $validator_user, $validator_department);
        }


        return $validation;
    }

    public function createApprobationProcess($validation, User $validator_user = null, Department $validator_department = null)
    {
        $approbation = new Approbation();
        $approbation->setValidation($validation);
        $approbation->setUserApprobator($validator_user);
        $approbation->setDepartmentApprobator($validator_department);
        $approbation->setApproval(false);

        $this->em->persist($approbation);

        $this->em->flush();

        return $approbation;
    }
}