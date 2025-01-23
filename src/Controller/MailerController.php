<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Service\EntityFetchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Repository\UserRepository;
use App\Repository\ValidationRepository;

use App\Service\MailerService;

class MailerController extends AbstractController
{

    private $em;
    private $logger;

    // Repository methods
    private $validationRepository;
    private $userRepository;


    // Services methods
    private $mailerService;
    private $entityFetchingService;




    public function __construct(

        EntityManagerInterface          $em,
        LoggerInterface                 $logger,

        // Repository methods
        ValidationRepository            $validationRepository,
        UserRepository                  $userRepository,


        // Services methods
        MailerService                   $mailerService,
        EntityFetchingService           $entityFetchingService,

    ) {
        $this->em                           = $em;
        $this->logger                       = $logger;

        // Variables related to the repositories
        $this->validationRepository         = $validationRepository;
        $this->userRepository               = $userRepository;

        // Variables related to the services
        $this->mailerService                = $mailerService;
        $this->entityFetchingService        = $entityFetchingService;
    }


    #[Route('/mail/testmail', name: 'test_mail')]
    public function testMail(): Response
    {
        $subject = "Test Email";
        $html = "<p>This is a test email</p>";
        $recipient = $this->userRepository->findOneBy(['username' => 'florian.dkhissi']);

        $message = $this->mailerService->sendEmail($recipient, $subject, $html);

        $this->addFlash('alert', $message);
        return $this->redirectToRoute('app_base');
    }

    #[Route('/mail/mailupdate', name: 'mail_update')]
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


    #[Route('/maildev', name: 'mail_dev')]
    public function devEmailAddress(): Response
    {

        if ($this->getParameter('kernel.environment') != 'dev') {
            $this->addFlash('warning', 'Change the environment to dev to change mail addresses to dev mode.');
            return $this->redirectToRoute('app_base');
        }

        $usersUpdated = [];
        $htmlContent = "<h1>Email Address Updates</h1>"; // Start your HTML content

        foreach ($this->entityFetchingService->getUsers() as $user) {
            $username = $user->getUsername();
            // $newEmail = "florian.dkhissi+{$username}@opmobility.com";
            $newEmail = "florian.dkhissi@opmobility.com";

            $oldEmail = $user->getEmailAddress();

            // Check if the new email already exists in the database
            $existingUser = $this->userRepository->findOneBy(['emailAddress' => $newEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->logger->warning("Email $newEmail already exists for another user. Skipping update for user $username.");
                continue; // Skip this user to avoid duplication
            }

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);


                $usersUpdated[] = $user;
                $emailAfterUpdate = $user->getEmailAddress();

                $htmlContent .= "<p>{$username}'s email address updated to {$emailAfterUpdate}</p>"; // Append to the HTML content
            }
        }
        // Persist and flush after all updates
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
            $this->addFlash('alert', 'Email addresses updated successfully. ' . $message);
            return $this->redirectToRoute('app_base'); // Optionally return the HTML content as a response
        }

        $this->addFlash('alert', 'No email addresses were updated');
        return $this->redirectToRoute('app_base');
    }
}
