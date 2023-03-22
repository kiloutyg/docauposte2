<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/', name: 'app_')]



class BaseController extends AbstractController
{
    private $categories = [
        [
            'zones' => ['Assemblage' => ['lignes' => ['D41', 'Demontage', 'Enjoliveur']], 'Déchargement Reprise' => ['lignes' => ['D41', 'DEFAUTHEQUE']]]
        ]
    ];

    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render('base.html.twig', [
            'categories' => $this->categories,
        ]);
    }



}