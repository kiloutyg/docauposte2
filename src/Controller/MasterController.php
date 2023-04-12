<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\AdminController;
use App\Controller\SecurityController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class MasterController extends AbstractController
{
    #[Route('/master', name: 'app_master')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();


        return $this->render('master/master_index.html.twig', [
            'controller_name' => 'MasterController',
            'error' => $error,
            'last_username' => $lastUsername,



        ]);
    }
}