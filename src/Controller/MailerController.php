<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Approbation;

class MailerController extends FrontController

{
    #[Route('/mailer/{approbationId}', name: 'app_mailer')]
    public function mailTemplateTester(int $approbationId): Response
    {

        $approbation = $this->approbationRepository->findOneBy(['id' => $approbationId]);

        $sender = $this->security->getUser();
        $senderEmail = $sender->getEmailAddress();

        $approbator = $approbation->getUserApprobator();
        $emailRecipientsAddress = $approbator->getEmailAddress();

        $validation = $approbation->getValidation();

        $upload = $validation->getUpload();

        $filename = $upload->getFilename();

        $approbations = [];
        $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);

        $approbators = [];
        foreach ($approbations as $soleApprobation) {
            $approbators[] = $soleApprobation->getUserApprobator();
        }

        return $this->render('/email_templates/approbationEmail.html.twig', [
            'upload'                    => $upload,
            'validation'                => $validation,
            'approbation'               => $approbation,
            'approbator'                => $approbator,
            'approbators'               => $approbators,
            'filename'                  => $filename,
            'senderEmail'               => $senderEmail,
            'emailRecipientsAddress'    => $emailRecipientsAddress,
        ]);
    }



    // #[Route('/email/{userId}')]
    // public function sendEmail(int $userId): Response
    // {
    //     $user = $this->userRepository->find($userId);

    //     $subject    = 'Test email';
    //     $text       = 'This is a test email';
    //     $html       = '<p>This is a test email</p>
    //                     <h1><strong>With a title</strong></h1>               
    //                     <p>With a second paragraph</p>
    //                     <p>And a third</p>
    //                     <a href="http://slanlp0033/login">Se connecter</a> ';
    //     $status     = $this->mailerService->sendEmail($user, $subject, $text, $html);

    //     if ($status) {
    //         $this->addFlash('success', 'Email sent!');
    //         return $this->redirectToRoute('app_base');
    //     } else {
    //         $this->addFlash('error', 'Email not sent! Error: ' . $status);
    //         return $this->redirectToRoute('app_base');
    //     }
    // }
}