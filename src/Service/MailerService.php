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

class MailerService extends AbstractController
{
    private $mailer;

    private $senderEmail;
    private $logger;

    private $userRepository;
    private $approbationRepository;


    public function __construct(
        MailerInterface $mailer,
        string $senderEmail,
        LoggerInterface $logger,
        UserRepository $userRepository,
        ApprobationRepository $approbationRepository
    ) {
        $this->mailer                   = $mailer;
        $this->senderEmail              = $senderEmail;
        $this->logger                   = $logger;

        $this->userRepository           = $userRepository;
        $this->approbationRepository   = $approbationRepository;
    }

    public function approbationEmail(Validation $validation)
    {
        $approbations = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);
        foreach ($approbations as $approbation) {
            $this->sendApprobationEmail($approbation);
        }
    }

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


    public function sendDisapprobationEmail(Validation $validation)
    {
        $upload = $validation->getUpload();
        $approbations = [];
        $approbators = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation, 'Approval' => false]);
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

    public function sendReminderEmailToUploader(User $uploader)
    {
        $senderEmail = $this->senderEmail;
        $emailRecipientsAddress = $uploader->getEmailAddress();

        $inValidationUploads = $uploader->getUploadsInValidation();
        // $this->logger->info('inValidationUploads', json_decode($inValidationUploads));
        $refusedValidationUploads = $uploader->getUploadsInRefusedValidation();
        // $this->logger->info('refusedValidationUploads', $refusedValidationUploads);

        // $totalUploads = array_merge($inValidationUploads, $refusedValidationUploads);

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Rappel des documents en cours de validation.')
            ->htmlTemplate('services/email_templates/reminderEmailToUploader.html.twig')
            ->context([
                'uploader'                          => $uploader,
                'waitingUploads'                    => $inValidationUploads,
                'refusedUploads'                    => $refusedValidationUploads,
                // 'totalUploads'                      => $totalUploads
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    // Function to send a reminder email to all users listing uploads in validation entire status
    public function sendReminderEmailToAllUsers(array $uploads)
    {
        $this->logger->info('uploads: ', [$uploads]);

        $usersRaw = $this->userRepository->findAll();
        foreach ($usersRaw as $user) {
            if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles()))
                $users[] = $user;
        };
        $uploaders = [];
        foreach ($uploads as $upload) {
            if (!in_array($upload->getUploader(), $uploaders)) {
                $uploaders[] = $upload->getUploader();
            }
        }

        $this->logger->info('users: ', [$users]);


        $senderEmail = $this->senderEmail;

        foreach ($users as $user) {
            $emailRecipientsAddresses[] = $user->getEmailAddress();
        }

        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to(...$emailRecipientsAddresses)
            ->subject('Docauposte - Rappel des documents en cours de validation.')
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
}