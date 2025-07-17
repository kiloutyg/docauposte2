<?php

namespace App\Controller\Operator;

use \Psr\Log\LoggerInterface;

use App\Controller\Operator\OperatorBaseController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        $this->logger->info(message: 'OperatorAdminController: operatorAdminPage - operators', context: [$operators]);

        // Create and handle forms
        foreach ($operators as $operator) {
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
     * Handles the editing of operator entities and displays the operator list with forms.
     *
     * This function processes operator editing requests and manages the display of operator forms.
     * If a specific operator is provided, it processes the form submission for that operator.
     * It then retrieves operators based on search criteria (either from POST request or session)
     * and creates forms for each operator. If no search criteria are found, it displays
     * deactivated operators as a fallback.
     *
     * @param Request $request The HTTP request object containing form data, search parameters,
     *                         and session information for operator retrieval and form processing
     * @param Operator|null $operator The specific operator entity to edit, or null if no specific
     *                                operator is being edited. When provided, triggers form processing
     *                                for that operator
     *
     * @return Response A rendered view containing the operator list component with forms for
     *                  editing operators, displaying either search results or deactivated operators
     */
    #[Route('/operator/edit/{operator}', name: 'app_operator_edit')]
    public function editOperatorAction(Request $request, ?Operator $operator = null): Response
    {
        $this->logger->notice('OperatorAdminController::editOperatorAction');

        $operators = [];
        $operatorForms = [];

        if ($operator) {
            $this->logger->notice('OperatorAdminController::editOperatorAction - operator', [$operator]);
            $this->operatorFormProcessing($operator, $request);
        }

        if ($request->isMethod('POST') && $request->request->get('search') == 'true') {
            $operators = $this->operatorService->operatorEntitySearchByRequest($request);

            $this->logger->info('OperatorAdminController::editOperatorAction - operators with request key search and post', [$operators]);

            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        } elseif ($request->getSession()->has('operatorSearchParams')) {

            $operators = $this->operatorService->operatorEntitySearchBySession($request);
            $this->logger->info('OperatorAdminController::editOperatorAction - operatorSearchParams operators', [$operators]);

            foreach ($operators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        } else {
            $this->logger->info('OperatorAdminController::editOperatorAction - operatorForms is empty');
            $inActiveOperators = $this->operatorRepository->findDeactivatedOperators();
            foreach ($inActiveOperators as $operator) {
                $operatorForms[$operator->getId()] = $this->createForm(OperatorType::class, $operator, [
                    'operator_id' => $operator->getId(),
                ])->createView();
            }
        }

        $this->logger->info('OperatorAdminController::editOperatorAction - render operatorForms ', [$operatorForms]);

        return $this->render('services/operators/admin_component/_adminListOperator.html.twig', [
            'operatorForms' => $operatorForms,
        ]);
    }




    /**
     * Processes the form submission for editing an operator entity.
     *
     * This private method creates and handles an operator form, validates the submitted data,
     * and attempts to update the operator through the operator service. It provides user
     * feedback through flash messages and logs the operation results for debugging purposes.
     * The method handles both successful updates and various error scenarios that may occur
     * during form processing or operator modification.
     *
     * @param Operator $operator The operator entity to be edited and updated
     * @param Request $request The HTTP request object containing the form data to be processed
     *
     * @return void This method does not return a value but modifies the operator entity
     *              and sets flash messages for user feedback
     */
    private function operatorFormProcessing($operator, $request): void
    {
        $form = $this->createForm(OperatorType::class, $operator, [
            'operator_id' => $operator->getId(),
        ]);

        $error = false;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->operatorService->editOperatorService($form, $operator);
                $this->addFlash('success', 'L\'opérateur a bien été modifié');
                $this->logger->notice('OperatorAdminController::operatorFormProcessing - Operator updated successfully', ['operator' => $operator]);
            } catch (\Exception $e) {
                $this->addFlash('danger', 'L\'opérateur n\'a pas pu être modifié. Erreur: ' . $e->getMessage());
                $this->logger->error('OperatorAdminController::operatorFormProcessing - Error while editing operator in try catch', [$e->getMessage()]);
                $error = true;
            }
        } else {
            $this->logger->error('OperatorAdminController::operatorFormProcessing - Error in submitting form while editing operator');
            $error = true;
        }

        if ($error) {
            $this->logger->error('OperatorAdminController::operatorFormProcessing - error true');
        }
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









    /**
     * Handles batch editing of multiple operators in a single request.
     *
     * This function processes form data for multiple operators simultaneously, validating
     * each operator's data through their respective forms and updating them via the
     * operator service. It provides detailed feedback about successful updates and any
     * validation or processing errors that occur during the batch operation.
     *
     * @param Request $request The HTTP request object containing the operators data array
     *                         with operator IDs as keys and their form data as values
     *
     * @return JsonResponse A JSON response containing:
     *                      - success: boolean indicating if any operators were successfully updated
     *                      - message: string with summary of the operation results
     *                      - successCount: integer count of successfully updated operators
     *                      - errorCount: integer count of operators that failed to update
     *                      - errors: array of detailed error messages for failed operations
     */
    #[Route('/operator/batch-edit', name: 'app_operator_batch_edit', methods: ['POST'])]
    public function batchEditOperators(Request $request): JsonResponse
    {
        $this->logger->debug('OperatorAdminController::batchEditOperators - request', ['request' => $request->request->all()]);

        try {
            $operatorsData = $request->request->all('operators');
            $this->logger->debug('OperatorAdminController::batchEditOperators - operatorsData', ['operatorsData' => $operatorsData]);
            $successCount = 0;
            $errors = [];

            foreach ($operatorsData as $operatorId => $operatorData) {
                try {
                    $operator = $this->operatorRepository->find($operatorId);
                    if (!$operator) {
                        $errors[] = "Opérateur avec l'ID {$operatorId} non trouvé";
                        continue;
                    }

                    $form = $this->createForm(OperatorType::class, $operator, [
                        'operator_id' => $operator->getId(),
                    ]);
                    $this->logger->debug('OperatorAdminController::batchEditOperators - form', ['form' => $form->getData()]);

                    $this->logger->debug('OperatorAdminController::batchEditOperators - form data', ['formData' => $operatorData]);
                    $form->submit($operatorData);

                    if ($form->isValid()) {
                        $this->operatorService->editOperatorService($form, $operator);
                        $successCount++;
                        $this->logger->debug('OperatorAdminController::batchEditOperators - operator updated successfully', ['operatorId' => $operatorId]);
                    } else {
                        // Get detailed form errors
                        $formErrors = [];
                        foreach ($form->getErrors(true) as $error) {
                            $formErrors[] = $error->getMessage();
                        }
                        $errorMessage = "Erreur de validation pour l'opérateur {$operatorId}: " . implode(', ', $formErrors);
                        $errors[] = $errorMessage;
                        $this->logger->error('OperatorAdminController::batchEditOperators - Form validation failed', [
                            'operatorId' => $operatorId,
                            'errors' => $formErrors,
                            'submittedData' => $operatorData
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Erreur lors de la modification de l'opérateur {$operatorId}: " . $e->getMessage();
                    $this->logger->error('OperatorAdminController::batchEditOperators - Batch edit error for operator ' . $operatorId, ['exception' => $errors]);
                }
            }

            $message = "{$successCount} opérateur(s) modifié(s) avec succès";
            if (!empty($errors)) {
                $message .= ". Erreurs: " . implode(', ', $errors);
            }

            return new JsonResponse([
                'success' => $successCount > 0,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => count($errors),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            $this->logger->error('OperatorAdminController::batchEditOperators - Batch edit operators error', ['exception' => $e]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la modification en lot: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Handles batch deletion of multiple operators in a single request.
     *
     * This function processes a JSON request containing an array of operator IDs and attempts
     * to delete each operator using the operatorBaseController's deletion method. It provides
     * detailed feedback about successful deletions and any errors that occur during the batch
     * operation, ensuring that partial failures don't prevent other deletions from proceeding.
     *
     * @param Request $request The HTTP request object containing JSON data with an 'operatorIds'
     *                         array of operator IDs to be deleted
     *
     * @return JsonResponse A JSON response containing:
     *                      - success: boolean indicating if any operators were successfully deleted
     *                      - message: string with summary of the operation results
     *                      - successCount: integer count of successfully deleted operators
     *                      - errorCount: integer count of operators that failed to delete
     *                      Returns HTTP 400 if no operators are selected, HTTP 500 for general errors
     */
    #[Route('/operator/batch-delete', name: 'app_operator_batch_delete', methods: ['POST'])]
    public function batchDeleteOperators(Request $request): JsonResponse
    {
        $this->logger->debug('OperatorAdminController::batchDeleteOperators - request', ['request' => $request->request->all()]);
        try {
            $data = json_decode($request->getContent(), true);
            $operatorIds = $data['operatorIds'] ?? [];

            if (empty($operatorIds)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Aucun opérateur sélectionné'
                ], 400);
            }

            $successCount = 0;
            $errors = [];

            foreach ($operatorIds as $operatorId) {
                try {
                    $this->operatorBaseController->deleteActionOperatorController('operator', $operatorId, $request);
                    // Assuming successful deletion if no exception is thrown
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Erreur lors de la suppression de l'opérateur {$operatorId}: " . $e->getMessage();
                    $this->logger->error('Batch delete error for operator ' . $operatorId, ['exception' => $e]);
                }
            }

            $message = "{$successCount} opérateur(s) supprimé(s) avec succès";
            if (!empty($errors)) {
                $message .= ". Erreurs: " . implode(', ', $errors);
            }

            return new JsonResponse([
                'success' => $successCount > 0,
                'message' => $message,
                'successCount' => $successCount,
                'errorCount' => count($errors)
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Batch delete operators error', ['exception' => $e]);
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression en lot: ' . $e->getMessage()
            ], 500);
        }
    }
}
