<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_')]

class BaseController extends AbstractController
{
    private function __construct()
    {
        // Empty construct function here for static.
    }
}
