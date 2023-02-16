<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;


#[Route('/', name: 'app_')]



class PhpController extends AbstractController

{
    // private $zones = [
    //     ['ligne' => 'D41', 'zones' => 'Assemblage'],
    //     ['ligne' => 'Démontage', 'zones' => 'Assemblage'],
    //     ['ligne' => 'Enjoliveur', 'zones' => 'Assemblage'],
    //     ['ligne' => 'D41', 'zones' => 'Déchargement Reprise'],
    //     ['ligne' => 'DEFAUTHEQUE', 'zones' => 'Déchargement Reprise'],
    // ];
    // private $zone = [array_column($zones, 'zones')];

    #[Route('/', name: 'basephp')]
    public function base(): Response
    {
        return $this->render('/php/basephp.html.twig');
    }
    #[Route('/demo', name: 'demo')]
    public function demo(): Response
    {
        return $this->render('/php/demo.php');
    }
}