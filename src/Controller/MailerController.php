<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;




class MailerController extends FrontController

{
    #[Route('/mail/testmail', name: 'testmail')]
    public function testMail(): Response
    {
        $subject = "Test Email";
        $html = "<p>This is a test email</p>";
        $recipient = $this->userRepository->findOneBy(['username' => 'florian.dkhissi']);

        $message = $this->mailerService->sendEmail($recipient, $subject, $html);

        $this->addFlash('alert', $message);
        return $this->redirectToRoute('app_base');
    }

    #[Route('/mail/mailadupdate', name: 'mailadupdate')]
    public function updateEmailAddress(): Response
    {
        $usersUpdated = [];
        $htmlContent = "<h1>Email Address Updates</h1>"; // Start your HTML content

        foreach ($this->users as $user) {
            $username = $user->getUsername();
            $newEmail = "{$username}@opmobility.com";
            $oldEmail = $user->getEmailAddress();

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);

                $this->em->persist($user);
                $this->em->flush();

                $usersUpdated[] = $user;
                $emailAfterUpdate = $user->getEmailAddress();

                $htmlContent .= "<p>{$username}'s email address updated to {$emailAfterUpdate}</p>"; // Append to the HTML content
            }
        }

        if (!empty($usersUpdated)) {
            // Send email or handle the response with the HTML content
            $subject = "Update Email Address";
            $recipient = $this->userRepository->findOneBy(['username' => 'florian.dkhissi']);
            if ($recipient) {
                $message = $this->mailerService->sendEmail($recipient, $subject, $htmlContent);
            }
            $this->addFlash('alert', 'Email addresses updated successfully' . $message);
            return $this->redirectToRoute('app_base'); // Optionally return the HTML content as a response
        }
        $this->addFlash('alert', 'No email addresses were updated');
        return $this->redirectToRoute('app_base');
    }

    #[Route('/mail/maildev', name: 'mailaddev')]
    public function devEmailAddress(): Response
    {
        $usersUpdated = [];
        $htmlContent = "<h1>Email Address Updates</h1>"; // Start your HTML content

        foreach ($this->users as $user) {
            $username = $user->getUsername();
            $newEmail = "florian.dkhissi+{$username}@opmobility.com";
            $oldEmail = $user->getEmailAddress();

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);

                $this->em->persist($user);
                $this->em->flush();

                $usersUpdated[] = $user;
                $emailAfterUpdate = $user->getEmailAddress();

                $htmlContent .= "<p>{$username}'s email address updated to {$emailAfterUpdate}</p>"; // Append to the HTML content
            }
        }

        if (!empty($usersUpdated)) {
            // Send email or handle the response with the HTML content
            $subject = "Update Email Address";
            $recipient = $this->userRepository->findOneBy(['username' => 'florian.dkhissi']);
            if ($recipient) {
                $message = $this->mailerService->sendEmail($recipient, $subject, $htmlContent);
            }
            $this->addFlash('alert', 'Email addresses updated successfully' . $message);
            return $this->redirectToRoute('app_base'); // Optionally return the HTML content as a response
        }
        $this->addFlash('alert', 'No email addresses were updated');
        return $this->redirectToRoute('app_base');
    }

    // test mail and method remindertouploader
    #[Route('/mail/mail_test', name: 'remindertouploader')]
    public function reminderToUploader(): Response
    {

        // $uploader = $this->userRepository->findOneBy(['username' => 'aamr.fadili']);
        $uploader = $this->userRepository->findOneBy(['username' => 'camille.gindrey']);

        $this->logger->info('uploader' . $uploader->getUsername());
        $message = $this->mailerService->sendReminderEmailToUploader($uploader);

        $this->addFlash('alert', $message);
        return $this->redirectToRoute('app_base');
    }
}
