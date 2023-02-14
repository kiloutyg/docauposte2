<?php

namespace App\Controller;



use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;


#[Route('/', name: 'app_')]



class HtmlController extends AbstractController
{
private $zones = [
    ['ligne' => 'D41', 'zones' => 'Assemblage'],
    ['ligne' => 'Démontage', 'zones' => 'Assemblage'],
    ['ligne' => 'Enjoliveur', 'zones' => 'Assemblage'],
    ['ligne' => 'D41', 'zones' => 'Déchargement Reprise'],
    ['ligne' => 'DEFAUTHEQUE', 'zones' => 'Déchargement Reprise'],
];
// private $zone = [array_column($zones, 'zones')];

    #[Route('/', name: 'base')]
    public function base(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('html/index.html.twig', [
            'title' => 'DocAuPoste',
            'zones' => $this->zones,
        ]);
    }

    #[Route('/sommere', name: 'sommere')]
    public function sommere(): Response
    {
        return $this->render('html/sommere.html.twig', [
            'title' => 'DocAuPoste',
            'zones' => $this->zones,
        ]);
    }

    #[Route('/table', name: 'table')]
    public function table(): Response
    {
    return $this->render('html/table.html.twig');
    }
    
    #[Route('/formulaire', name: 'formulaire')]
    public function formulaire(): Response
    {
    return $this->render('html/forms.html.twig');
    }

    #[Route('/upload', name: 'upload')]
    public function upload(): Response
    {
        return $this->render('html/upload.html.twig');
    }
}