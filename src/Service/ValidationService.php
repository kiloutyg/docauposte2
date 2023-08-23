<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;


use App\Repository\UploadRepository;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use App\Repository\ValidationRepository;
use App\Repository\ApprobationRepository;

use App\Entity\Upload;
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
        $this->logger                   = $logger;
        $this->em                       = $em;
        $this->uploadRepository         = $uploadRepository;
        $this->departmentRepository     = $departmentRepository;
        $this->userRepository           = $userRepository;
        $this->validationRepository     = $validationRepository;
        $this->approbationRepository    = $approbationRepository;
    }

    public function createValidation(Upload $upload, Request $request)
    {
        // Create empty arrays to store values for validator_department and validator_user
        $validator_user_values = [];
        $this->logger->info('request: ' . json_encode($request->request->all()));
        // Iterate through the keys in the request
        foreach ($request->request->keys() as $key) {
            // If the key contains 'validator_user', add its value to the validator_user_values array
            if (strpos($key, 'validator_user') !== false && $request->request->get($key) !== null && $request->request->get($key) !== '') {
                $validator_user_values[] = $request->request->get($key);
            }
        }

        // Create a new Validation instance
        $validation = new Validation();

        // Set the Upload object on the Validation instance
        $validation->setUpload($upload);

        // Set the status of the Validation instance to false
        $validation->setStatus(false);

        // Persist the Validation instance to the database
        $this->em->persist($validation);

        // Flush changes to the database
        $this->em->flush();

        // Initialize variables for validator_user and validator_department
        $validator_user = null;

        // Loop through each validator_user value
        foreach ($validator_user_values as $validator_user_value) {
            // If the validator_user value is not null
            if ($validator_user_value !== null || $validator_user_value !== '') {
                // Find the User entity using the repository and the validator_user value
                $validator_user = $this->userRepository->find($validator_user_value);
            }
            $this->logger->info('validator_user_value: ' . $validator_user_value);
            // Call the createApprobationProcess method to create an Approbation process
            $this->createApprobationProcess(
                $validation,
                $validator_user,
            );
            $validator_user = null;
        }
        // Return the Validation instance
        return $validation;
    }

    public function createApprobationProcess(
        $validation,
        User $validator_user = null,
    ) {
        // Create a new Approbation instance
        $approbation = new Approbation();
        // Set the Validation object on the Approbation instance
        $approbation->setValidation($validation);
        // Set the UserApprobator object on the Approbation instance
        if ($validator_user !== null || $validator_user !== '') {
            $approbation->setUserApprobator($validator_user);
        }

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();

        // Return the Approbation instance
        return $approbation;
    }

    public function validationApproval(Approbation $approbation, Request $request)
    {
        // Get the value of the 'approvalRadio' input from the request
        $approvalStr = $request->request->get('approvalRadio');
        // Convert the value to a boolean
        $approval = filter_var($approvalStr, FILTER_VALIDATE_BOOLEAN);
        // Get the value of the 'approbationComment' input from the request
        $comment = $request->request->get('approbationComment');

        // Set the Approval property of the Approbation instance
        $approbation->setApproval($approval);
        // Set the Comment property of the Approbation instance
        $approbation->setComment($comment);

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();

        // Get the Validation object associated with the Approbation instance
        $validation = $approbation->getValidation();

        // Call the approbationCheck method to check if all approbations are approved
        $this->approbationCheck($validation);

        // No need to return anything
        return;
    }


    public function approbationCheck(Validation $validation)
    {
        // Get the ID of the Validation instance
        $validationId = $validation->getId();
        // Create an empty array to store Approbation instances
        $approbations = [];
        // Find all Approbation instances associated with the Validation ID
        $approbations = $this->approbationRepository->findBy(['Validation' => $validationId]);
        // Loop through each Approbation instance
        foreach ($approbations as $approbation) {
            // If the Approbation is not approved or the Approval property is null
            if ($approbation->isApproval() == false || $approbation->isApproval() == null) {
                // Set the status of the Validation instance to false
                $validation->setStatus(false);
                // Persist the Validation instance to the database
                $this->em->persist($validation);
                // Flush changes to the database
                $this->em->flush();
                $upload = $validation->getUpload();
                $upload->setValidated(false);
                $this->em->persist($upload);
                $this->em->flush();
                // Return early
                return;
            }
        }
        // If all Approbations are approved, set the status of the Validation instance to true
        $validation->setStatus(true);
        // Persist the Validation instance to the database
        $this->em->persist($validation);
        // Flush changes to the database
        $this->em->flush();

        $upload = $validation->getUpload();
        $upload->setValidated(true);
        $this->em->persist($upload);
        $this->em->flush();

        // Return early
        return;
    }

    public function resetApprobation(Upload $upload, Request $request)
    {
        if ($upload->getValidation() == null) {
            return;
        }
        // Set the validated property of the Upload instance to null
        $upload->setValidated(null);
        // Get the ID of the validation instance
        $validation = $upload->getValidation();
        // Remove the Validation instance from the database
        $validation->setStatus(null);
        // Persist the Validation instance to the database
        $this->em->persist($validation);
        // Flush changes to the database
        $this->em->flush();
        // Create an empty array to store Approbation instances
        $approbations = [];
        // Get the ID of the Validation instance
        $approbations = $validation->getApprobations();
        // Loop through each Approbation instance
        foreach ($approbations as $approbation) {
            //If it's a major modification reset all approbations
            if ($request->request->get('modification-outlined') == 'heavy-modification') {
                // Remove the Approbation instance from the database
                $approbation->setApproval(null);
                $approbation->setComment(null);
            }
            // If the Approbation is not approved or the Approval property is null
            elseif ($approbation->isApproval() == false) {
                // Remove the Approbation instance from the database
                $approbation->setApproval(null);
                $approbation->setComment(null);
            }
        }

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();
        // Return early
        return;
    }
}