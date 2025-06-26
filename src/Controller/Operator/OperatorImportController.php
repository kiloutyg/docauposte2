<?php

namespace App\Controller\Operator;

use App\Service\Operator\OperatorService;
use App\Service\Operator\OperatorImportService;

use \Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;

class OperatorImportController extends AbstractController
{
    private $logger;
    private $operatorService;
    private $operatorImportService;


    public function __construct(
        LoggerInterface                 $logger,
        OperatorService                 $operatorService,
        OperatorImportService           $operatorImportService
    ) {
        $this->logger                           = $logger;

        $this->operatorService                  = $operatorService;
        $this->operatorImportService            = $operatorImportService;
    }




    #[Route('/operator/import', name: 'app_operator_import')]
    public function importOpe(Request $request, ValidatorInterface $validator)
    {
        $this->operatorService->teamUapInitialization();
        try {
            $response = $this->operatorImportService->importOpeService($request);
            $this->addFlash('success', $response);
        } catch (\Exception $e) {
            $this->logger->error('Error importing operators', [
                'error' => $e->getMessage()
            ]);
            $this->addFlash('danger', 'Les opérateurs n\'ont pas pu être importés. Erreur: ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_operator');
    }
}
