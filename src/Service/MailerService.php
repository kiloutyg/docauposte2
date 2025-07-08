<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

use Psr\Log\LoggerInterface;

use App\Entity\Validation;
use App\Entity\User;
use App\Entity\Approbation;

use App\Repository\ApprobationRepository;
use App\Repository\UserRepository;
use App\Repository\DepartmentRepository;

use App\Service\EntityFetchingService;

class MailerService extends AbstractController
{
    private $mailer;

    private $senderEmail;
    private $logger;

    private $userRepository;
    private $approbationRepository;
    private   $departmentRepository;

    private $entityFetchingService;

    public function __construct(
        string $senderEmail,

        MailerInterface                 $mailer,
        LoggerInterface                 $logger,

        UserRepository                  $userRepository,
        ApprobationRepository           $approbationRepository,
        DepartmentRepository            $departmentRepository,

        EntityFetchingService           $entityFetchingService,
    ) {
        $this->mailer                   = $mailer;
        $this->senderEmail              = $senderEmail;
        $this->logger                   = $logger;

        $this->userRepository           = $userRepository;
        $this->approbationRepository    = $approbationRepository;
        $this->departmentRepository     = $departmentRepository;

        $this->entityFetchingService    = $entityFetchingService;
    }

    /**
     * Sends an email notification to all users involved in the given validation process.
     *
     * @param Validation $validation The validation entity for which the email notification is to be sent.
     *
     * @return void
     */
    public function approbationEmail(Validation $validation)
    {
        $approbations = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);
        foreach ($approbations as $approbation) {
            $this->sendApprobationEmail($approbation);
        }
    }

    /**
     * Sends an email to a specific user with custom subject and HTML content.
     *
     * @param User $recipient The user entity who will receive the email
     * @param string $subject The subject line of the email
     * @param string $html The HTML content of the email body
     *
     * @return bool|string Returns true if the email was sent successfully,
     *                     or the error message string if sending failed
     */
    public function sendEmail(User $recipient, string $subject, string $html)
    {

        $emailRecipientsAddress = $recipient->getEmailAddress();
        $email = (new Email())
            ->from($this->senderEmail)
            ->to($emailRecipientsAddress)
            ->subject($subject)
            ->html($html);
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }


    /**
     * Sends an approbation email notification to a specific approbator for document validation.
     *
     * This method sends a templated email to an approbator notifying them that a document
     * requires their validation. The email includes context about the upload, validation,
     * and other approbators involved in the process.
     *
     * @param Approbation $approbation The approbation entity containing the approbator and validation details
     *
     * @return bool|string Returns true if the email was sent successfully,
     *                     or the error message string if sending failed
     */
    public function sendApprobationEmail($approbation)
    {
        $senderEmail = $this->senderEmail;
        $approbator = $approbation->getUserApprobator();
        $emailRecipientsAddress = $approbator->getEmailAddress();
        $validation = $approbation->getValidation();
        $upload = $validation->getUpload();
        $filename = $upload->getFilename();
        $approbations = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);
        $approbators = [];
        foreach ($approbations as $soleApprobation) {
            if ($soleApprobation->getUserApprobator() != $approbator) {
                $approbators[] = $soleApprobation->getUserApprobator();
            }
        }
        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Nouvelle validation à effectuer du document ' . $filename)
            ->htmlTemplate('services/email_templates/approbationEmail.html.twig')
            ->context([
                'upload'                    => $upload,
                'validation'                => $validation,
                'approbation'               => $approbation,
                'approbator'                => $approbator,
                'approbators'               => $approbators,
                'filename'                  => $filename,
                'senderEmail'               => $senderEmail,
                'emailRecipientsAddress'    => $emailRecipientsAddress,
            ]);
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }


    /**
     * Sends a disapprobation email notification to the document uploader when their document has been rejected.
     *
     * This method notifies the original uploader that their document has been disapproved by one or more
     * approbators. It retrieves all disapproved approbations for the validation and sends a templated
     * email containing details about the rejection.
     *
     * @param Validation $validation The validation entity containing the document that was disapproved
     *
     * @return string|null Returns the error message string if sending failed, or null if successful
     */
    public function sendDisapprobationEmail(Validation $validation)
    {
        $upload = $validation->getUpload();
        $approbations = [];
        $approbators = [];
        $approbations = $this->approbationRepository->findBy([
            'Validation' => $validation,
            'approval' => false
        ]);
        foreach ($approbations as $approbation) {
            $approbators[] = $approbation->getUserApprobator();
        }
        $senderEmail = $this->senderEmail;
        $emailRecipientsAddress = $upload->getUploader()->getEmailAddress();
        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Le document ' . $upload->getFilename() . ' a été refusé.')
            ->htmlTemplate('services/email_templates/disapprobationEmail.html.twig')
            ->context([
                'upload'                    => $upload,
                'approbations'              => $approbations
            ]);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }



    /**
     * Sends an email notification to an approbator when a previously disapproved document has been modified/corrected.
     *
     * This method notifies the approbator who had previously disapproved a document that the document
     * has now been corrected by the uploader. The email contains information about the modified upload
     * and uses a templated email format.
     *
     * @param Approbation $approbation The approbation entity containing the approbator who will be notified
     *                                 and the validation details of the corrected document
     *
     * @return bool|string Returns true if the email was sent successfully,
     *                     or the error message string if sending failed
     */
    public function sendDisapprovedModifiedEmail(Approbation $approbation)
    {

        $senderEmail = $this->senderEmail;
        $validation = $approbation->getValidation();
        $upload = $validation->getUpload();

        $filename = $upload->getFilename();

        $user = $approbation->getUserApprobator();
        $emailRecipientsAddress = $user->getEmailAddress();

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Le document ' . $filename . ' a été corrigé.')
            ->htmlTemplate('services/email_templates/disapprovedModifiedEmail.html.twig')
            ->context([
                'upload'                    => $upload,
                'filename'                  => $filename
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    /**
     * Sends an approval email notification to the document uploader when their document has been validated.
     *
     * This method notifies the original uploader that their document has been successfully approved
     * and validated by all required approbators. The email includes details about the approved upload
     * and uses a templated email format.
     *
     * @param Validation $validation The validation entity containing the document that was approved
     *
     * @return bool|string Returns true if the email was sent successfully,
     *                     or the error message string if sending failed
     */
    public function sendApprovalEmail(Validation $validation)
    {
        $upload = $validation->getUpload();
        $senderEmail = $this->senderEmail;
        $emailRecipientsAddress = $upload->getUploader()->getEmailAddress();
        $filename = $upload->getFilename();

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Le document ' . $filename . ' a été validé.')
            ->htmlTemplate('services/email_templates/approvalEmail.html.twig')
            ->context([
                'upload'                    => $upload
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }

    /**
     * Sends a reminder email notification to a specific user about pending document validations.
     *
     * This method sends a templated email to a user reminding them about documents that are
     * currently awaiting their validation. The email includes a list of uploads that require
     * the recipient's attention for approval or disapproval.
     *
     * @param User $Recipient The user entity who will receive the reminder email notification
     * @param array $uploads An array of upload entities that are pending validation by the recipient
     *
     * @return bool Returns true if the email was sent successfully, false if sending failed
     */
    public function sendReminderEmail(User $Recipient, array $uploads)
    {
        $senderEmail = $this->senderEmail;
        $emailRecipientsAddress = $Recipient->getEmailAddress();

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Rappel de validation en cours')
            ->htmlTemplate('services/email_templates/reminderEmail.html.twig')
            ->context([
                'uploads'                    => $uploads
            ]);
        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    /**
     * Sends a reminder email notification to an uploader about their documents' validation status.
     *
     * This method sends a templated email to a document uploader reminding them about their uploads
     * that are currently in validation process or have been refused. The email includes both
     * documents waiting for validation and documents that have been rejected and need correction.
     *
     * @param User $uploader The user entity who uploaded the documents and will receive the reminder email
     *
     * @return bool Returns true if the email was sent successfully, false if sending failed
     */
    public function sendReminderEmailToUploader(User $uploader)
    {
        $senderEmail = $this->senderEmail;
        $emailRecipientsAddress = $uploader->getEmailAddress();

        $inValidationUploads = $uploader->getUploadsInValidation();
        $this->logger->info('MailerService::sendReminderEmailToUploader - inValidationUploads', [$inValidationUploads]);

        $refusedValidationUploads = $uploader->getUploadsInRefusedValidation();
        $this->logger->info('MailerService::sendReminderEmailToUploader - refusedValidationUploads', [$refusedValidationUploads]);

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Rappel des documents en cours de validation.')
            ->htmlTemplate('services/email_templates/reminderEmailToUploader.html.twig')
            ->context([
                'uploader'                          => $uploader,
                'waitingUploads'                    => $inValidationUploads,
                'refusedUploads'                    => $refusedValidationUploads,
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    // Function to send a reminder email to all users listing uploads in validation entire status
    /**
     * Sends a reminder email notification to all users (excluding super admins) about pending document validations.
     *
     * This method sends a templated email to all non-super-admin users in the system, providing them
     * with a comprehensive overview of all uploads currently in validation status. The email includes
     * both the list of uploads awaiting validation and the unique uploaders who submitted these documents.
     * Super admin users are excluded from receiving this notification.
     *
     * @param array $uploads An array of upload entities that are currently pending validation across the system
     *
     * @return bool Returns true if the email was sent successfully to all recipients, false if sending failed
     */
    public function sendReminderEmailToAllUsers(array $uploads)
    {
        $this->logger->debug('MailerService::sendReminderEmailToAllUsers - uploads: ', [$uploads]);

        $usersRaw = $this->userRepository->findAll();
        foreach ($usersRaw as $user) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                $users[] = $user;
            }
        }

        $uploaders = [];
        foreach ($uploads as $upload) {
            if (!in_array($upload->getUploader(), $uploaders)) {
                $uploaders[] = $upload->getUploader();
            }
        }

        $this->logger->debug('MailerService::sendReminderEmailToAllUsers - users: ', [$users]);

        $senderEmail = $this->senderEmail;

        foreach ($users as $user) {
            $emailRecipientsAddresses[] = $user->getEmailAddress();
        }

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to(...$emailRecipientsAddresses)
            ->subject('Docauposte - Rappel de toutes les actions en cours.')
            ->htmlTemplate('services/email_templates/reminderEmailToAll.html.twig')
            ->context([
                'uploads'                    => $uploads,
                'uploaders'                  => $uploaders
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }


    // public function monthlyQualityResume(array $emailRecipientsAddresses, array $groupedValidatedUploads)
    /**
     * Sends a monthly quality resume email to all users in the QUALITY department.
     *
     * This method retrieves all validated uploads with their associations and sends a summary
     * email to all users belonging to the QUALITY department. The email contains a comprehensive
     * list of all documents that were validated during the previous period, allowing quality
     * staff to review and track document validation activities.
     *
     * @return bool Returns true if the email was sent successfully to all quality department users,
     *              false if sending failed due to transport exceptions
     */
    public function monthlyQualityResume()
    {
        $uploads = $this->entityFetchingService->getAllValidatedUploadsWithAssociations();

        $users = $this->departmentRepository->findOneBy(['name' => 'QUALITY'])->getUsers();

        foreach ($users as $user) {
            $emailRecipientsAddresses[] = $user->getEmailAddress();
        }
        $email = (new TemplatedEmail())
            ->from($this->senderEmail)
            ->to(...$emailRecipientsAddresses)
            ->subject('Docauposte - Rappel de tous les documents validés durant la précedente période.')
            ->htmlTemplate('services/email_templates/validatedUploadResumeToQualityStaff.html.twig')
            ->context([
                'uploads'                    => $uploads
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}
