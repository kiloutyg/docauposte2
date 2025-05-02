<?php

namespace App\Controller\Support;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use App\Service\EntityFetchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Repository\UserRepository;
use App\Repository\ValidationRepository;

use App\Service\MailerService;

/**
 * MailerController
 *
 * This controller manages email-related functionality in the application,
 * including sending test emails and updating user email addresses.
 * It provides utilities for both production and development environments.
 */
class MailerController extends AbstractController
{
    /**
     * @var EntityManagerInterface Entity manager for database operations
     */
    private $em;
    
    /**
     * @var LoggerInterface Logger for recording email-related operations
     */
    private $logger;

    /**
     * @var UserRepository Repository for User entity operations
     */
    private $userRepository;

    /**
     * @var MailerService Service for sending emails
     */
    private $mailerService;
    
    /**
     * @var EntityFetchingService Service for fetching entities
     */
    private $entityFetchingService;

    /**
     * Constructor for MailerController
     *
     * Initializes all required services and repositories for email management.
     *
     * @param EntityManagerInterface $em Entity manager for database operations
     * @param LoggerInterface $logger Logger for recording operations
     * @param UserRepository $userRepository Repository for User entity operations
     * @param MailerService $mailerService Service for sending emails
     * @param EntityFetchingService $entityFetchingService Service for fetching entities
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,

        // Repository methods
        UserRepository $userRepository,
        
        // Services methods
        MailerService $mailerService,
        EntityFetchingService $entityFetchingService,
    ) {
        $this->em = $em;
        $this->logger = $logger;

        // Variables related to the repositories
        $this->userRepository = $userRepository;

        // Variables related to the services
        $this->mailerService = $mailerService;
        $this->entityFetchingService = $entityFetchingService;
    }

    /**
     * Sends a test email
     *
     * This method sends a simple test email to a predefined user
     * to verify that the email system is working correctly.
     *
     * @return Response A redirect to the homepage with status message
     */
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

    /**
     * Updates email addresses for all users
     *
     * This method standardizes email addresses for all users in the system
     * to follow the format username@opmobility.com. It skips users who already
     * have the correct email format or where the new email would cause a conflict.
     *
     * @return Response A redirect to the homepage with status message
     */
    #[Route('/mail/mailupdate', name: 'mail_update')]
    public function updateEmailAddress(): Response
    {
        $usersUpdated = [];
        $htmlContent = "<h1>Email Address Updates</h1>"; // Start your HTML content

        foreach ($this->entityFetchingService->getUsers() as $user) {
            $username = $user->getUsername();
            $newEmail = "{$username}@opmobility.com";
            $oldEmail = $user->getEmailAddress();

            // Check if the new email already exists in the database
            $existingUser = $this->userRepository->findOneBy(['emailAddress' => $newEmail]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                $this->logger->warning("Email $newEmail already exists for another user.");
                continue; // Skip this user to avoid duplication
            }

            if ($oldEmail !== $newEmail) {
                $user->setEmailAddress($newEmail);

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

    /**
     * Updates email addresses for development environment
     *
     * This method changes all user email addresses to a development format
     * (florian.dkhissi+username@opmobility.com) to facilitate testing without
     * sending emails to real users. This operation is restricted to the development
     * environment and super admin users for security reasons.
     *
     * @return Response A redirect to the homepage with status message
     */
    #[Route('/mail/maildev', name: 'mail_dev')]
    public function devEmailAddress(): Response
    {
        if ($this->getParameter('kernel.environment') != 'dev' || !$this->isGranted('ROLE_SUPER_ADMIN')) {
            $this->addFlash('warning', 'Change the environment to dev to change mail addresses to dev mode.');
            return $this->redirectToRoute('app_base');
        }

        $usersUpdated = [];
        $htmlContent = "<h1>Email Address Updates</h1>"; // Start your HTML content

        foreach ($this->entityFetchingService->getUsers() as $user) {
            $username = $user->getUsername();
            $newEmail = "florian.dkhissi+{$username}@opmobility.com";
 
            $oldEmail = $user->getEmailAddress();

            // Check if the new email already exists in the database
            $existingUser = $this->userRepository->findOneBy(['emailAddress' => $newEmail]);
            if (
                ($existingUser && $existingUser->getId() !== $user->getId()) ||
                in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)
            ) {
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
