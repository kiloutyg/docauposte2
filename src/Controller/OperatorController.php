<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Form\OperatorType;

use App\Entity\Operator;

class OperatorController extends FrontController{

    // Route to have the basic page being display for dev purpose
    #[Route('/operator', name: 'app_operator')]
    public function operatorBasePage(): Response
    {

        if ($this->operatorsRepository->findAll() != null) {
            $operators = $this->operatorRepository->findAll();
        }
        $operator = new Operator();
        
        $operatorForm = $this->createForm(OperatorType::class, $operator);

        return $this->render('services/operators/operators.html.twig', [
            'operatorForm' => $operatorForm->createView(),
        ]);
    }
}