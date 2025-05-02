<?php

namespace App\Controller\Operator;

use \Psr\Log\LoggerInterface;

use App\Controller\Operator\OperatorBaseController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use App\Form\OperatorType;
use App\Form\TeamType;
use App\Form\UapType;

use App\Entity\Operator;
use App\Entity\Team;
use App\Entity\Uap;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Repository\OperatorRepository;

use App\Service\EntityFetchingService;
use App\Service\PdfGeneratorService;
use App\Service\OperatorService;


class OperatorAdminController extends AbstractController
{

    public $logger;
    public $authChecker;
    public $operatorBaseController;

    // Repository methods
    public $operatorRepository;

    // Services methods
    public $pdfGeneratorService;
    public $entityFetchingService;
    public $operatorService;



    public function __construct(
        LoggerInterface                 $logger,
        AuthorizationCheckerInterface   $authChecker,
        OperatorBaseController          $operatorBaseController,

        // Repository classes
        OperatorRepository              $operatorRepository,

        // Services classes
        PdfGeneratorService             $pdfGeneratorService,
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,

    ) {
        $this->logger                       = $logger;
        $this->authChecker                  = $authChecker;
        $this->operatorBaseController       = $operatorBaseController;

        // Variables related to the repositories
        $this->operatorRepository           = $operatorRepository;

        // Variables related to the services
        $this->pdfGeneratorService          = $pdfGeneratorService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
    }


    #[Route('/operator/admin', name: 'app_operator')]
    public function operatorAdminPage(Request $request): Response
    {
        $newOperator = new Operator();
        $newOperatorForm = $this->createForm(OperatorType::class, $newOperator);
        $newOperatorForm->handleRequest($request);

        if ($newOperatorForm->isSubmitted() && $newOperatorForm->isValid()) {
            try {
                $newOperatorId = $this->operatorService->processNewOperatorFromFormType($newOperator, $newOperatorForm);
                $this->addFlash('success', 'L\'opérateur a bien été ajouté');
                // Generate the print detail URL
                $printUrl = $this->generateUrl('app_operator_detail', ['operatorId' => $newOperatorId]);

                // Store the print URL in the session
                $request->getSession()->set('print_operator_url', $printUrl);

                // Redirect to app_operator with a special parameter
                return $this->redirectToRoute('app_operator', ['open_print' => true]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être ajouté' . $e->getMessage());
                return $this->redirectToRoute('app_operator');
            }
        }

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorService->operatorEntitySearch($request);
        } else {
            $operators = [];
        }

        $operatorForms = [];
        $this->logger->info('operators', [$operators]);

        // Create and handle forms
        foreach ($operators as $operator) {
            $this->logger->info('operator', [$operator->getUaps()->getValues()]);

            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ])->createView();
        }

        if (empty($operatorForms)) {
            $inActiveOperators = $this->operatorRepository->findDeactivatedOperators();
            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }

        return $this->render('services/operators/operators_admin.html.twig', [
            'newOperatorForm'   => $newOperatorForm->createView(),
            'operatorForms'     => $operatorForms,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
        ]);
    }




    // Individual operator modification controller, used in dev purpose
    #[Route('/operator/edit/{id}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, Operator $operator): Response
    {
        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorService->operatorEntitySearch($request);
            $operatorForms = [];

            // Create and handle forms
            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }

        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $error = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operatorService->editOperatorService($form, $operator);
                $this->addFlash('success', 'L\'opérateur a bien été modifié');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être modifié. Erreur: ' . $e->getMessage());
                $this->logger->error('Error while editing operator in try catch', [$e->getMessage()]);
                $error = true;
            }
        } else {
            $this->logger->error('Error in submitting form while editing operator');
            $error = true;
        }

        if ($error) {
            $this->logger->error('error true');
            $operatorForms = [$operator->getId() => $form->createView()];
        } elseif (isset($operatorForms)) {
            $this->logger->debug('operatorsForms isset');
        } else {
            $this->logger->debug('there is only one operator', [[$operator], [$operator->getUaps()->getValues()]]);
            $operatorForms = [];
        }
        return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
            'form' => $form->createView() ?? null,
            'operator' => $operator ?? null,
            'operatorForms' => $operatorForms,
        ]);
    }





    // Route to delete operator from the administrator view
    #[Route('/operator/delete/{id}', name: 'app_operator_delete')]
    public function deleteOperatorAction(int $id, Request $request): Response
    {
        return $this->operatorBaseController->deleteActionOperatorController('operator', $id, $request);
    }




    // Route to print the operator detail in a pdf
    #[Route('/operator/detail/{operatorId}', name: 'app_operator_detail')]
    public function printOpeDetail(int $operatorId)
    {
        $operator = $this->operatorRepository->find($operatorId);
        $this->pdfGeneratorService->generateOperatorPdf($operator);
        return true;
    }




    #[Route('/operator/operator_team_or_uap_management', name: 'app_operator_team_or_uap_management')]
    public function operatorTeamUapManagement(Request $request): Response
    {
        $this->operatorService->teamUapInitialization();

        $team = new Team();
        $uap = new Uap();
        $teamForm = $this->createForm(TeamType::class, $team);
        $uapForm = $this->createForm(UapType::class, $uap);

        if ($request->getMethod() == 'GET' || !$this->authChecker->IsGranted('ROLE_ADMIN')) {
            return $this->render('services/operators/team_uap_operator_management.html.twig', [
                'teams'     => $this->entityFetchingService->getTeams(),
                'uaps'      => $this->entityFetchingService->getUaps(),
                'teamForm'  => $teamForm->createView(),
                'uapForm'   => $uapForm->createView()
            ]);
        } else {
            $this->operatorService->operatorTeamUapFormManagement($uapForm, $teamForm, $request);
            return $this->redirect($request->headers->get('referer'));
        }
    }



    // Route to delete UAP or Team without breaking operators and training records database
    #[Route('/operator/delete-uap-or-team/{entityType}/{entityId}', name: 'app_delete_uap_or_team')]
    public function deleteUapTeamProperly(string $entityType, int $entityId, Request $request): Response
    {
        return $this->operatorBaseController->deleteActionOperatorController($entityType, $entityId, $request);
    }
}
