<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Service\EntityFetchingService;


class MailerController extends FrontController
{
    private $entityFetchingService;
    
    public function __construct(
        EntityFetchingService $entityFetchingService,
    ) {
        parent::__construct();
        $this->entityFetchingService = $entityFetchingService;
    }


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

        foreach ($this->entityFetchingService->getUsers() as $user) {
            $username = $user->getUsername();
            // $this->logger->info('username: ' . $username);
            $newEmail = "{$username}@opmobility.com";
            $oldEmail = $user->getEmailAddress();
            // $this->logger->info('oldEmail: ' . $oldEmail);
            // $this->logger->info('newEmail: ' . $newEmail);

            // Check if the new email already exists in the database
            $existingUser = $this->userRepository->findOneBy(['emailAddress' => $newEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->logger->warning("Email $newEmail already exists for another user.");
                continue; // Skip this user to avoid duplication
            }

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);
                // $this->logger->info('user email now: ' . $user->getEmailAddress());

                // Persist and flush inside the loop is not efficient, should be done outside
                // $this->em->persist($user);
                // $this->em->flush();

                $usersUpdated[] = $user;
                $emailAfterUpdate = $user->getEmailAddress();

                $htmlContent .= "<p>{$username}'s email address updated to {$emailAfterUpdate}</p>"; // Append to the HTML content
            }
        }

        if (!empty($usersUpdated)) {

            foreach ($usersUpdated as $updatedUser) {
                $this->em->persist($updatedUser);
            }
            $this->em->flush();


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

        foreach ($this->entityFetchingService->getUsers() as $user) {
            $username = $user->getUsername();
            // $this->logger->info('username: ' . $username);
            $newEmail = "florian.dkhissi+{$username}@opmobility.com";
            $oldEmail = $user->getEmailAddress();
            // $this->logger->info('oldEmail: ' . $oldEmail);
            // $this->logger->info('newEmail: ' . $newEmail);

            // Check if the new email already exists in the database
            $existingUser = $this->userRepository->findOneBy(['emailAddress' => $newEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->logger->warning("Email $newEmail already exists for another user. Skipping update for user $username.");
                continue; // Skip this user to avoid duplication
            }

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);
                // $this->logger->info('user email now: ' . $user->getEmailAddress());

                // Persist and flush inside the loop is not efficient, should be done outside
                // $this->em->persist($user);
                // $this->em->flush();

                $usersUpdated[] = $user;
                $emailAfterUpdate = $user->getEmailAddress();

                $htmlContent .= "<p>{$username}'s email address updated to {$emailAfterUpdate}</p>"; // Append to the HTML content
            }
        }

        // Persist and flush after all updates
        if (!empty($usersUpdated)) {
            foreach ($usersUpdated as $updatedUser) {
                // $this->logger->info('updatedUser: ', [$updatedUser]);
                $this->em->persist($updatedUser);
            }
            $this->em->flush();

            // Send email or handle the response with the HTML content
            $subject = "Update Email Address";
            $recipient = $this->userRepository->findOneBy(['username' => 'florian.dkhissi']);
            if ($recipient) {
                $message = $this->mailerService->sendEmail($recipient, $subject, $htmlContent);
            }
            $this->addFlash('alert', 'Email addresses updated successfully. ' . $message);
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

        // $this->logger->info('uploader' . $uploader->getUsername());
        $message = $this->mailerService->sendReminderEmailToUploader($uploader);

        $this->addFlash('alert', $message);
        return $this->redirectToRoute('app_base');
    }


    // A route to test the sendReminderEmailToAllUsers method
    #[Route('/mail/mail_test_reminder', name: 'mail_test_reminder')]
    public function testReminderEmailToUploader(): Response
    {
        // $nonValidatedValidations[] = $this->validationRepository->findBy(['status' => !true]);
        $nonValidatedValidations = $this->validationRepository->findNonValidatedValidations();
        // $this->logger->info('nonValidatedValidations: ', [$nonValidatedValidations]);

        foreach ($nonValidatedValidations as $validation) {
            $uploads[] = $validation->getUpload();
        }
        // $this->logger->info('uploads: ', [$uploads]);

        $this->mailerService->sendReminderEmailToAllUsers($uploads);
        return $this->redirectToRoute('app_base');
    }
}
