<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\print_var_name;
use function Symfony\Component\String\u;


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