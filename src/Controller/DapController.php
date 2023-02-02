<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class DapController extends AbstractController
{
    #[Route('/', methods: ['GET'])]

    public function homepage(): Response
    {
        return new Response('Title : Doc Au Poste II');
        // die('DocAuPoste 2 est le renouveau de l\'application metier de gestion et mise a disposition des documents aux operateurs sur les lignes de productions');
        // return new Response('Title : Doc Au Poste II');
    }

    #[Route('/test', methods: ['POST'])]

    public function post (): Response
    {
        return new Response('Title : Doc Au Poste II :');
    }

}