<?php

namespace App\Controller;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\FrontController;

class ViewsModificationController extends FrontController
{
    #[Route('/viewmod/base', name: 'app_base_views_modification')]
    public function baseViewModificationPageView(): Response
    {

        return $this->render('views_modification/base_views_modification.html.twig', [
            'controller_name' => 'ViewsModificationController',
        ]);
    }

    #[Route('/viewmod/modifying', name: 'app_views_modification')]
    public function viewsModification(Request $request)
    {
    }
}