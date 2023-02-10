<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]

class HtmlController extends AbstractController
{
    #[Route('/', name: 'index')]

    public function index(): Response
    {
        return $this->render('html/index.html.twig');
    }

}