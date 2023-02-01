<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DapController extends AbstractController

{
    #[Route('/', methods: ['GET'])]

    public function homepage()
    {
        die('DocAuPoste 2 est le renouveau de l\'application metier de gestion et mise a disposition des documents aux operateurs sur les lignes de productions');

    }

        
}