<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/', name: 'front_')]
class DapController extends AbstractController
{
    #[Route(path:'/', name: 'index')]

    public function homepage(): Response
    {
        // die('Title : Doc Au Poste II');
        // die('DocAuPoste 2 est le renouveau de l\'application metier de gestion et mise a disposition des documents aux operateurs sur les lignes de productions');
        // return new Response('Title : Doc Au Poste II');
        return $this->render('dapcontroller/index.html.twig', [
            'controller_name' => 'Dapcontroller',
        ]);
    }

}