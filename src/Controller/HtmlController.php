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

    #[Route('/sommere', name: 'sommere')]

    public function sommere(): Response
    {
        // return $this->render('html/sommere.html.twig');
        $zones = [
            ['ligne' => 'D41', 'zones' => 'Assemblage'],
            ['ligne' => 'Démontage', 'zones' => 'Assemblage'],
            ['ligne' => 'Enjoliveur', 'zones' => 'Assemblage'],
            ['ligne' => 'D41', 'zones' => 'Déchargement Reprise'],
            ['ligne' => 'DEFAUTHEQUE', 'zones' => 'Déchargement Reprise'],

            // 'Demontage - Assemblage',
            // 'Enjoliveur P51 - Assemblage',
            // 'P54 - Assemblage',
            // 'P8 MV - Assemblage',
            // 'Panneaux - Assemblage',
            // 'T9 MV - Assemblage',
            // 'D41 - Déchargement Reprise',
            // 'DEFAUTHEQUE - Déchargement Reprise',
            // 'GESTION-DR - Déchargement Reprise',
            // 'LUX-DR - Déchargement Reprise',
            // 'MATRICES-ILUO - Déchargement Reprise',
            // 'P87 - Déchargement Reprise',
        ];
        // Method to dump the specified variables : 
        // dump($zones);
        // dd($zones);

        return $this->render('html/sommere.html.twig', [
            'title' => 'DocAuPoste',
            'zones' => $zones,
        // $html = $twig->render('Dap/homepage.html.twig', [
        //     'title' => 'DocAuPoste',
        //     'zones' => $zones,
        ]);
    }

}