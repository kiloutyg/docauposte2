<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;

use App\Repository\UserRepository;
use App\Repository\ValidationRepository;

use App\Entity\Validation;
use App\Entity\User;
use App\Entity\Upload;
use App\Entity\Approbation;
use App\Repository\ApprobationRepository;

class MailerService extends AbstractController
{
    private $security;
    private $userRepository;
    private $mailer;
    private $validationRepository;
    private $logger;
    private $approbationRepository;

    public function __construct(
        Security $security,
        UserRepository $userRepository,
        MailerInterface $mailer,
        ValidationRepository $validationRepository,
        LoggerInterface $logger,
        ApprobationRepository $approbationRepository
    ) {
        $this->security             = $security;
        $this->userRepository       = $userRepository;
        $this->mailer               = $mailer;
        $this->validationRepository = $validationRepository;
        $this->logger               = $logger;
        $this->approbationRepository = $approbationRepository;
    }

    public function sendEmail(User $recipient, string $subject, string $html)
    {
        $sender = $this->security->getUser();
        $senderEmail = $sender->getEmailAddress();

        $emailRecipientsAddress = $recipient->getEmailAddress();

        $email = (new Email())
            ->from($senderEmail)
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

    public function sendApprobationEmail(int $approbationId)
    {

        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);

        $senderEmail = 'lan.docauposte@plasticomnium.com';

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
}