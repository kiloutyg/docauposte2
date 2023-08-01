<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ValidationController extends FrontController
{
    #[Route('/validation', name: 'app_validation')]
    public function index(): Response
    {
        return $this->render('services/validation/validation.html.twig', [
            'controller_name' => 'ValidationController',
        ]);
    }
}