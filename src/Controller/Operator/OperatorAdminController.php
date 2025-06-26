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
use App\Service\Operator\PdfGeneratorService;
use App\Service\Operator\OperatorService;


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


    /**
     * Renders the operator administration page.
     *
     * This function handles the creation of new operators and the search for existing ones.
     * It processes form submissions for new operators, handles search requests, and prepares
     * forms for displaying and editing operators. If no search results are found, it displays
     * deactivated operators.
     *
     * @param Request $request The HTTP request object containing form data, search parameters,
     *                         and session information
     *
     * @return Response A rendered view containing forms for creating and editing operators,
     *                  along with search results or deactivated operators if no search was performed
     */
    #[Route('/operator/admin', name: 'app_operator')]
    public function operatorAdminPage(Request $request): Response
    {

        $this->logger->info('OperatorAdminController: operatorAdminPage');

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
            $operators = $this->operatorService->operatorEntitySearchByRequest($request);
        } elseif ($request->getSession()->has('operatorSearchParams')) {
            $operators = $this->operatorService->operatorEntitySearchBySession($request);
        } else {
            $operators = [];
        }


        $operatorForms = [];
        $this->logger->info('OperatorAdminController: operatorAdminPage - operators', [$operators]);

        // Create and handle forms
        foreach ($operators as $operator) {
            $this->logger->info('OperatorAdminController: operatorAdminPage - operator', [$operator->getUaps()->getValues()]);

            $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ])->createView();
        }

        if (empty($operatorForms)) {
            $this->logger->info('OperatorAdminController: operatorAdminPage - operatorForms is empty');
            $inActiveOperators = $this->operatorRepository->findDeactivatedOperators();
            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }

        $this->logger->info('OperatorAdminController::operatorAdminPage', ['operators' => $operators, 'operatorForms' => $operatorForms]);

        return $this->render('services/operators/operators_admin.html.twig', [
            'newOperatorForm'   => $newOperatorForm->createView(),
            'operatorForms'     => $operatorForms,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
        ]);
    }




    /**
     * Handles the editing of an operator entity.
     *
     * This function processes both search requests and form submissions for operator editing.
     * If a search request is detected, it retrieves matching operators and creates forms for each.
     * For form submissions, it attempts to update the operator and provides appropriate feedback.
     *
     * @param Request $request The HTTP request object containing form data or search parameters
     * @param Operator $operator The operator entity to be edited (auto-wired by Symfony)
     * @param int|null $id Optional operator ID to fetch the operator if not provided via auto-wiring
     *
     * @return Response A rendered view containing the operator edit form or search results
     */
    #[Route('/operator/edit/{operator}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, ?Operator $operator = null): Response
    {

        $this->logger->info('OperatorAdminController::editOperatorAction', ['operator' => $operator]);

        $operators = [];
        $operatorForms = [];
        $form = null;


        if ($operator) {

            $form = $this->createForm(OperatorType::class, $operator, [
                'operator_id' => $operator->getId(),
            ]);

            $error = false;
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->operatorService->editOperatorService($form, $operator);
                    $this->addFlash('success', 'L\'opérateur a bien été modifié');
                    $this->logger->notice('Operator updated successfully', ['operator' => $operator]);
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
            }
        }


        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorService->operatorEntitySearchByRequest($request);

            $this->logger->info('OperatorAdminController: editOperatorAction - operators with request key search', [$operators]);

            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
                $this->logger->info('OperatorAdminController: editOperatorAction - operator', [$operator]);
            }
        } elseif ($request->getSession()->has('operatorSearchParams')) {
            $this->logger->info('OperatorAdminController: editOperatorAction - operatorSearchParams', [$request->getSession()->get('operatorSearchParams')]);
            $operators = $this->operatorService->operatorEntitySearchBySession($request);
            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
                $this->logger->info('OperatorAdminController: editOperatorAction - operator', [$operator]);
            }
        }



        $this->logger->info('OperatorAdminController::editOperatorAction', ['operators' => $operators, 'operatorForms' => $operatorForms]);
        return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
            'form' => $form?->createView(),
            'operator' => $operator ?? null,
            'operatorForms' => $operatorForms,
        ]);
    }





    // Route to delete operator from the administrator view
    /**
     * Handles the deletion of an operator entity.
     *
     * This function delegates the deletion process to the operatorBaseController's
     * deleteActionOperatorController method, specifying that an 'operator' entity
     * should be deleted.
     *
     * @param int $id The unique identifier of the operator to be deleted
     * @param Request $request The HTTP request object that may contain additional parameters
     *
     * @return Response A response object that typically redirects to a confirmation page
     *                  or back to the operator listing with a status message
     */
    #[Route('/operator/delete/{id}', name: 'app_operator_delete')]
    public function deleteOperatorAction(int $id, Request $request): Response
    {
        return $this->operatorBaseController->deleteActionOperatorController('operator', $id, $request);
    }




    // Route to print the operator detail in a pdf
    /**
     * Generates and outputs a PDF document containing detailed information about a specific operator.
     *
     * This function retrieves an operator entity by its ID and uses the PDF generator service
     * to create a detailed PDF document for that operator. The PDF is automatically sent to the browser.
     *
     * @param int $operatorId The unique identifier of the operator for whom to generate the PDF
     *
     * @return bool Returns true when the PDF has been successfully generated and output
     */
    #[Route('/operator/detail/{operatorId}', name: 'app_operator_detail')]
    public function printOpeDetail(int $operatorId): bool
    {
        $operator = $this->operatorRepository->find($operatorId);
        $this->pdfGeneratorService->generateOperatorPdf($operator);
        return true;
    }




    /**
     * Manages the creation and administration of teams and UAPs (Unit Assembly Production).
     *
     * This function handles the display and processing of forms for creating new teams and UAPs.
     * It initializes the necessary data structures, creates empty entities and their corresponding forms,
     * and either displays the management interface or processes form submissions based on the request method
     * and user permissions.
     *
     * @param Request $request The HTTP request object containing form data and method information
     *
     * @return Response A rendered view containing team and UAP management forms for GET requests
     *                  or a redirect response after processing form submissions for POST requests
     */
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
    /**
     * Handles the deletion of UAP or Team entities while preserving database integrity.
     *
     * This function delegates the deletion process to the operatorBaseController's
     * deleteActionOperatorController method, ensuring that related operators and
     * training records are properly handled during the deletion process.
     *
     * @param string $entityType The type of entity to delete ('uap' or 'team')
     * @param int $entityId The unique identifier of the entity to be deleted
     * @param Request $request The HTTP request object that may contain additional parameters
     *
     * @return Response A response object that typically redirects to a confirmation page
     *                  or back to the management page with a status message
     */
    #[Route('/operator/delete-uap-or-team/{entityType}/{entityId}', name: 'app_delete_uap_or_team')]
    public function deleteUapTeamProperly(string $entityType, int $entityId, Request $request): Response
    {
        return $this->operatorBaseController->deleteActionOperatorController($entityType, $entityId, $request);
    }
}
