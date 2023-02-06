<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'home')]
class Dapcontroller2Controller extends AbstractController
{
    #[Route('/', name: 'app_dapcontroller2')]
    public function index(): Response
    {
        return $this->render('dapcontroller2/index.html.twig', [
            'controller_name' => 'Dapcontroller2Controller',
        ]);

        #[Route('/test', methods: ['Response'])]
        function test(): Response
            {
                return new Response('Title : Doc Au Poste ptet, on verra :');
            };
    }

}
