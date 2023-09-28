<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Approbation;

class MailerController extends FrontController

{
    // #[Route('/mailer/test', name: 'app_mailer')]
    // public function mailTemplateTester()
    // {
    //     $validation = $this->validations[0];
    //     $this->mailerService->sendDisapprobationEmail($validation);
    //     $this->mailerService->sendApprovalEmail($validation);
    //     $approbations = [];
    //     $approbations = $this->approbationRepository->findBy(['Validation' => $validation]);
    //     foreach ($approbations as $approbation) {
    //         $this->mailerService->sendApprobationEmail($approbation);
    //         $this->mailerService->sendDisapprovedModifiedEmail($approbation);
    //     }
    //     return $this->redirectToRoute('app_base');
    // }
}