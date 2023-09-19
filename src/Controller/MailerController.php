<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Approbation;

class MailerController extends FrontController

{
    #[Route('/mailer/{validationId}', name: 'app_mailer')]
    public function mailTemplateTester(int $validationId)
    {
        $validation = $this->validationRepository->findOneBy(['id' => $validationId]);

        $this->mailerService->sendDisapprobationEmail($validation);

        return $this->redirectToRoute('app_base');
    }
}