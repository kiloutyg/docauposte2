<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use App\Repository\UserRepository;

use App\Entity\User;

class MailerService extends AbstractController
{
    private $security;
    private $userRepository;
    private $mailer;

    public function __construct(
        Security $security,
        UserRepository $userRepository,
        MailerInterface $mailer
    ) {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
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
}