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

class MailerService extends AbstractController
{
    private $security;
    private $userRepository;
    private $mailer;
    private $validationRepository;
    private $logger;

    public function __construct(
        Security $security,
        UserRepository $userRepository,
        MailerInterface $mailer,
        ValidationRepository $validationRepository,
        LoggerInterface $logger
    ) {
        $this->security             = $security;
        $this->userRepository       = $userRepository;
        $this->mailer               = $mailer;
        $this->validationRepository = $validationRepository;
        $this->logger               = $logger;
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

    public function sendApprobationEmail(Approbation $approbation)
    {
        $sender = $this->security->getUser();
        $senderEmail = $sender->getEmailAddress();

        $emailRecipientsAddress = $approbation->getUserApprobator()->getEmailAddress();

        $validation = $approbation->getValidation();
        $upload = $validation->getUpload();
        $filename = $upload->getFilename();

        $approbators = [];

        $approbations = $validation->getApprobations();
        foreach ($approbations as $approbation) {
            $approbators[] = $approbation->getUserApprobator();
        }


        $email = (new TemplatedEmail())
            ->from($senderEmail)
            ->to($emailRecipientsAddress)
            ->subject('Docauposte - Nouvelle validation à effectuer du document ' . $filename)
            ->htmlTemplate('email_templates/approbationEmail.html.twig')
            ->context([
                'upload' => $upload,
                'validation' => $validation,
                'approbations' => $approbations,
                'approbators' => $approbators,
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }
    }
}