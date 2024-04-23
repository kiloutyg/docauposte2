<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\Common\Collections\Collection;


use App\Entity\Validation;
use App\Entity\User;
use App\Entity\Approbation;
use App\Entity\Upload;

use App\Repository\ApprobationRepository;

class MailerService extends AbstractController
{
    private $security;
    private $mailer;
    private $approbationRepository;
    private $senderEmail;

    public function __construct(
        Security $security,
        MailerInterface $mailer,
        ApprobationRepository $approbationRepository,
        string $senderEmail
    ) {
        $this->security                 = $security;
        $this->mailer                   = $mailer;
        $this->approbationRepository    = $approbationRepository;
        $this->senderEmail              = $senderEmail;
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
        // $sender = $this->security->getUser();
        // $senderEmail = $sender->getEmailAddress();
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
            ->htmlTemplate('email_templates/approbationEmail.html.twig')
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
            ->htmlTemplate('email_templates/disapprobationEmail.html.twig')
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
            ->htmlTemplate('email_templates/disapprovedModifiedEmail.html.twig')
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
            ->htmlTemplate('email_templates/approvalEmail.html.twig')
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
            ->htmlTemplate('email_templates/reminderEmail.html.twig')
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

    // public function sendReminderEmailToUploader(array $badValidators)
    // {
    //     $senderEmail = $this->senderEmail;

    // }
}
