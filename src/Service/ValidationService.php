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

use App\Service\MailerService;
use App\Service\OldUploadService;


class ValidationService extends AbstractController
{
    protected $logger;
    protected $em;
    protected $uploadRepository;
    protected $departmentRepository;
    protected $userRepository;
    protected $validationRepository;
    protected $approbationRepository;
    protected $mailerService;
    protected $oldUploadService;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        UploadRepository $uploadRepository,
        DepartmentRepository $departmentRepository,
        UserRepository $userRepository,
        ValidationRepository $validationRepository,
        ApprobationRepository $approbationRepository,
        MailerService $mailerService,
        OldUploadService $oldUploadService
    ) {
        $this->logger                = $logger;
        $this->em                    = $em;
        $this->uploadRepository      = $uploadRepository;
        $this->departmentRepository  = $departmentRepository;
        $this->userRepository        = $userRepository;
        $this->validationRepository  = $validationRepository;
        $this->approbationRepository = $approbationRepository;
        $this->mailerService         = $mailerService;
        $this->oldUploadService      = $oldUploadService;
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
        $validation->setStatus(null);

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
            // Call the createApprobationProcess method to create an Approbation process
            $this->createApprobationProcess(
                $validation,
                $validator_user
            );
            $validator_user = null;
        }

        // Send a notification email to the validator
        $this->approbationEmail($validation);

        // Return the Validation instance
        return $validation;
    }


    public function createApprobationProcess(
        $validation,
        User $validator_user = null
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

        // Set the Approval Date property of the Approbation instance
        $approbation->setApprovedAt(new \DateTime());

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
        $status       = null;
        // Loop through each Approbation instance
        foreach ($approbations as $approbation) {
            // If the Approbation is not approved or the Approval property is null
            if ($approbation->isApproval() === false) {
                $status = false;
                $this->updateValidationAndUploadStatus($validation, $status);
                $this->disapprobationEmail($validation, $approbation->getUserApprobator(), $approbation->getComment());
                return;
            } else if ($approbation->isApproval() === true) {
                $status = true;
            } else if ($approbation->isApproval() === null) {
                $status = null;
                return;
            }
        }
        $this->updateValidationAndUploadStatus($validation, $status);
        return;
    }

    // This method will also activate the notification email to the uploader
    public function updateValidationAndUploadStatus(Validation $validation, ?bool $status)
    {
        if ($validation->isStatus() === false) {
            return;
        }
        // Set the status of the Validation instance to true
        $validation->setStatus($status);
        // Set the Validated Date property of the Validation instance
        $validation->setValidatedAt(new \DateTime());
        // Persist the Validation instance to the database
        $this->em->persist($validation);
        // Flush changes to the database
        $this->em->flush();
        $upload = $validation->getUpload();
        $upload->setValidated($status);
        // Delete the previously retired file if it exists
        if ($upload->getOldUpload() !== null) {
            $oldUpload = $upload->getOldUpload()->getId();
            $this->oldUploadService->deleteOldFile($oldUpload);
        }
        $upload->setOldUpload(null);
        $this->em->persist($upload);
        $this->em->flush();

        $this->approvalEmail($validation);
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
                $approbation->setApprovedAt(null);
                $approbation->setComment(null);
                $approbator = $approbation->getUserApprobator();
                $this->disapprovedModifiedEmail($validation, $approbator);
            }
            // If the Approbation is not approved or the Approval property is null
            elseif ($approbation->isApproval() == false) {
                // Remove the Approbation instance from the database
                $approbation->setApproval(null);
                $approbation->setApprovedAt(null);
                $approbation->setComment(null);
                $approbator = $approbation->getUserApprobator();
                $this->disapprovedModifiedEmail($validation, $approbator);
            }
        }

        // Persist the Approbation instance to the database
        $this->em->persist($approbation);

        // Flush changes to the database
        $this->em->flush();
        // Return early
        return;
    }

    public function approbationEmail(Validation $validation)
    {

        $approbations = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);
        foreach ($approbations as $approbation) {
            $approbationId = $approbation->getId();
            $this->mailerService->sendApprobationEmail($approbationId);
        }
    }


    public function disapprobationEmail(Validation $validation, User $user, string $comment = null)
    {
        $upload = $validation->getUpload();
        $filename = $upload->getFilename();
        $approbatorName = $user->getUsername();
        $recipient = $upload->getUploader();

        $subject = 'Docauposte - Le document ' . $filename . ' a été refusé par ' . $approbatorName . '.';
        if ($comment !== null) {
            $html = "<p> Bonjour, </p>
                    <p> Votre document $filename a été refusé par $approbatorName.'</p>
                    <p> Avec le commentaire de refus suivant : ' $comment '</p>
                    <p> Vous pouvez accéder au document en vous connectant à l'application en cliquant sur le lien suivant : <a class='btn-info' href='http://slanlp0033/login'>Page de connexion</a></p>
                    <p> Cordialement, </p>";
        }
        $html = "<p> Bonjour, </p>
        <p> Votre document $filename a été refusé par $approbatorName.'</p>
        <p> Vous pouvez accéder au document en vous connectant à l'application en cliquant sur le lien suivant : <a class='btn-info' href='http://slanlp0033/login'>Page de connexion</a></p>
        <p> Cordialement </p>";

        $this->mailerService->sendEmail($recipient, $subject, $html);
    }


    public function disapprovedModifiedEmail(Validation $validation, User $user)
    {
        $upload = $validation->getUpload();
        $filename = $upload->getFilename();
        $uploader = $upload->getUploader();
        $uploaderName = $uploader->getUsername();

        $subject = 'Docauposte - Validation suite à modification ' . $filename;
        $html = "<p> Bonjour, </p>
        <p> Vous avez une validation à effectuer d'une nouvelle version du document $filename qui a été uploadé par $uploaderName consécutivement à votre refus.</p>
        <p> Vous pouvez accéder au document en vous connectant à l'application en cliquant sur le lien suivant : <a class='btn-info' href='http://slanlp0033/login'>Page de connexion</a></p>
        <p> Cordialement </p>";

        $this->mailerService->sendEmail($user, $subject, $html);
    }


    public function approvalEmail(Validation $validation)
    {
        $upload = $validation->getUpload();
        $filename = $upload->getFilename();
        $uploader = $upload->getUploader();

        $subject = 'Docauposte - Le document ' . $filename . ' a été validé';
        $html = "<p> Bonjour, </p>
        <p> Le document $filename a été accepté par les valideurs. Il est désormais disponible à la consultation publique. </p>
        <p> Vous pouvez accéder au document en vous connectant à l'application en cliquant sur le lien suivant : <a class='btn-info' href='http://slanlp0033/login'>Page de connexion</a></p>
        <p> Cordialement </p>";

        $this->mailerService->sendEmail($uploader, $subject, $html);
    }
}