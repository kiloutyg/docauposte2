<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;



class MailerController extends FrontController

{
    #[Route('/mailer', name: 'app_mailer')]
    public function index(): Response
    {
        return $this->render('mailer/index.html.twig', [
            'controller_name' => 'MailerController',
        ]);
    }

    #[Route('/email/{userId}')]
    public function sendEmail(int $userId): Response
    {
        $user = $this->userRepository->find($userId);

        $subject    = 'Test email';
        $text       = 'This is a test email';
        $html       = '<p>This is a test email</p>
                        <h1><strong>With a title</strong></h1>               
                        <p>With a second paragraph</p>
                        <p>And a third</p>
                        <a href="http://slanlp0033/login">Se connecter</a> ';
        $status     = $this->mailerService->sendEmail($user, $subject, $text, $html);

        if ($status) {
            $this->addFlash('success', 'Email sent!');
            return $this->redirectToRoute('app_base');
        } else {
            $this->addFlash('error', 'Email not sent! Error: ' . $status);
            return $this->redirectToRoute('app_base');
        }
    }
}