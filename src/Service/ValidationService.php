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

use App\Entity\Upload;
use App\Entity\Department;
use App\Entity\User;
use App\Entity\Validation;



class ValidationService extends AbstractController

{
    protected $logger;
    protected $em;
    protected $uploadRepository;
    protected $departmentRepository;
    protected $userRepository;
    protected $validationRepository;

    public function __construct(
        LoggerInterface         $logger,
        EntityManagerInterface  $em,
        UploadRepository        $uploadRepository,
        DepartmentRepository    $departmentRepository,
        UserRepository          $userRepository,
        ValidationRepository    $validationRepository
    ) {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->uploadRepository     = $uploadRepository;
        $this->departmentRepository = $departmentRepository;
        $this->userRepository       = $userRepository;
        $this->validationRepository = $validationRepository;
    }
    public function createValidation(Upload $upload, Request $request)
    {


        $validation = new Validation();

        $validation->setUpload($upload);

        $validation->setStatus('Pending');

        $this->em->persist($validation);

        $this->em->flush();

        return $validation;
    }
}