<?php

namespace App\Controller\Support;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// This controller is responsible for rendering the tutorial interface
class TutorialController extends AbstractController
{
    #[Route('/tutorial', name: 'app_tutorial')]
    public function index(): Response
    {
        return $this->render('tutorial/tutorial_index.html.twig');
    }
}
