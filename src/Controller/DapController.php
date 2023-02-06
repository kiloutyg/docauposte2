<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use function Symfony\Component\String\u;

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

    // #[Route('/browse', name: 'browse')]
    // public function browse(): Response
    // {
    //     return new Response('Title : Doc Au Poste ptet, on verra :');
    // }
    #[Route('/browse/{slug}', name: 'browse_genre')]
    public function search(string $slug = null): Response
    {
        // $title = 'Doc Au Poste : '.$slug;
        // $title = str_replace('-', ' ', $slug);

       if ($slug) {
            $title = 'Zone : ' .u(str_replace('-', ' ', $slug))->title(true);
        } else {
            $title = 'Doc Au Poste';
        }
        return new Response($title);
       
        // $title = u(str_replace('-', ' ', $slug))->title(true);
        
        // return new Response('Title : Doc Au Poste pour '.$title);
    }

}