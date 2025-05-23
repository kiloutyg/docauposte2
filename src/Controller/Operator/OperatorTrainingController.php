<?php

namespace App\Controller\Operator;

use App\Service\EntityFetchingService;
use App\Service\TrainingRecordService;
use App\Service\OperatorService;

use \Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OperatorTrainingController extends AbstractController
{
    public $logger;

    // Services methods
    public $trainingRecordService;
    public $entityFetchingService;
    public $operatorService;
    public function __construct(
        LoggerInterface                 $logger,
        // Services classes
        TrainingRecordService           $trainingRecordService,
        EntityFetchingService           $entityFetchingService,
        OperatorService                 $operatorService,
    ) {
        $this->logger                       = $logger;

        // Variables related to the services
        $this->trainingRecordService        = $trainingRecordService;
        $this->entityFetchingService        = $entityFetchingService;
        $this->operatorService              = $operatorService;
    }






    // page with the training record and the operator list and the form to add a new operator,
    // page that will be integrated as an iframe probably in the test document page
    /**
     * Displays the training records for a specific upload, along with the operator list and a form to add a new operator.
     *
     * @Route("operator/traininglist/{uploadId}", name="app_training_list")
     *
     * @param int $uploadId The ID of the upload for which to display the training records.
     *
     * @return Response The rendered template with the training records, upload, teams, UAPs, and operators.
     */
    #[Route('operator/traininglist/{uploadId}', name: 'app_training_list')]
    public function trainingList(int $uploadId): Response
    {
        $upload = $this->entityFetchingService->find('upload', $uploadId);
        $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByUpload($upload);
        $this->logger->info('trainingRecords ', [$trainingRecords]);

        return $this->render('services/operators/operatorTraining.html.twig', [
            'trainingRecords'   => $trainingRecords,
            'upload'            => $upload,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
            'operators'         => $this->entityFetchingService->getOperators(),
        ]);
    }




    // Route to handle the newOperator form submission
    /**
     * Handles the creation of a new operator from the training list form submission.
     *
     * This method validates the operator name, creates a new operator with the provided
     * information, and redirects back to the training records page.
     *
     * @param Request $request The HTTP request containing the new operator data
     * @param int $uploadId The ID of the upload associated with the training record
     * @param int $teamId The ID of the team to which the new operator belongs
     * @param int $uapId The ID of the UAP (Unit Assembly Process) to which the new operator belongs
     *
     * @return Response A redirect response to the training records page
     */
    #[Route('/operator/traininglist/newOperator/{uploadId}/{teamId}/{uapId}', name: 'app_training_new_operator')]
    public function trainingListNewOperator(Request $request, int $uploadId, int $teamId, int $uapId): Response
    {
        if (!$this->operatorService->autoOperatorNameCheckerFromRequest($request)) {
            $this->addFlash('danger', 'Il y a eu un probleme avec le nom, contactez votre administrateur');
            return $this->redirectToRoute('app_render_training_records', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }

        $operatorCode = (int)$request->request->get('newOperatorCode');
        $operatorName = $request->request->get('newOperatorName');

        $this->operatorService->processOperatorFromRequest($operatorName, $operatorCode, $teamId, $uapId);

        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }





    /**
     * Handles the form submission for selecting a team and UAP to view training records.
     *
     * This method processes the form data from the training list page, validates that both
     * team and UAP have been selected, and redirects to the appropriate page to display
     * the filtered training records.
     *
     * @param Request $request The HTTP request containing the form data with team and UAP selections
     * @param int $uploadId The ID of the upload for which to display training records
     *
     * @return Response A redirect response either back to the form (if validation fails) or to the
     *                  training records display page with the selected filters
     */
    #[Route('/operator/traininglist/listform/{uploadId}', name: 'app_training_list_select_record_form')]
    public function trainingListSelectFormHandling(Request $request, int $uploadId): Response
    {
        $teamId = $request->request->get('team-trainingRecord-select');
        $uapId = $request->request->get('uap-trainingRecord-select');
        if ($teamId == null || $uapId == null) {
            $this->addFlash('danger', 'Veuillez sélectionner une équipe et une UAP');
            return $this->redirectToRoute('app_training_list', ['uploadId' => $uploadId]);
        }

        return $this->redirectToRoute('app_render_training_records', [
            'uploadId' => $uploadId,
            'teamId' => $teamId,
            'uapId' => $uapId,
        ]);
    }




    /**
     * Renders the training records view for operators filtered by team and UAP.
     *
     * This method retrieves operators based on the selected team and UAP, then processes their
     * training records to categorize them as trained, in training, or untrained. It organizes
     * operators by their trainers and prepares data for display in the template.
     *
     * @param int      $uploadId The ID of the upload containing the training records to display
     * @param int|null $teamId   The ID of the team to filter operators by, null for all teams
     * @param int|null $uapId    The ID of the UAP (Unit Assembly Process) to filter operators by, null for all UAPs
     *
     * @return Response A rendered template displaying the training records and operator categorizations
     */
    #[Route('/operator/render-training-records/{uploadId}/{teamId}/{uapId}', name: 'app_render_training_records')]
    public function renderTrainingRecords(int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {
        $selectedOperators = $this->entityFetchingService->findOperatorsByTeamAndUapId($teamId, $uapId);
        $this->logger->info('selectedOperators: ', [$selectedOperators]);

        $trainingRecords = []; // Array of training records
        $unorderedTrainingRecords = []; // Array of unordered training records
        $untrainedOperators = []; // Array of untrained operators
        $operatorsByTrainer = []; // Array of operators grouped by trainer
        $inTrainingOperatorsByTrainer = []; // Array of operators in training grouped by trainer

        foreach ($selectedOperators as $operator) {
            $records = $this->entityFetchingService->findBy('trainingRecord', ['operator' => $operator, 'upload' => $uploadId]);
            $this->logger->debug('records: ', [$records]);
            $unorderedTrainingRecords = array_merge($trainingRecords, $records);

            $record = $records[0] ?? null;
            $this->logger->debug('record: ', [$record]);

            if ($record) {
                $trainerName = $record->getTrainer() ? $record->getTrainer()->getOperator()->getName() : 'inconnu.nom';
                if ($record->isTrained()) {
                    $operatorsByTrainer[$trainerName][] = $operator;
                } else {
                    $inTrainingOperatorsByTrainer[$trainerName][] = $operator;
                }
            } else {
                $untrainedOperators[] = $operator;
            }
        }

        if (!empty($unorderedTrainingRecords)) {
            $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByTrainingRecordsArray($unorderedTrainingRecords);
        }

        $this->logger->info('untrainedOperators: ', [$untrainedOperators]);
        $this->logger->info('operatorsByTrainer: ', [$operatorsByTrainer]);
        $this->logger->info('inTrainingOperatorsByTrainer: ', [$inTrainingOperatorsByTrainer]);
        $this->logger->info('trainingRecords: ', [$trainingRecords]);
        $this->logger->info('unorderedTrainingRecords', [$unorderedTrainingRecords]);

        return $this->render('services/operators/training_component/_listOperatorContainer.html.twig', [
            'team' => $this->entityFetchingService->find('team', $teamId),
            'uap' => $this->entityFetchingService->find('uap', $uapId),
            'upload' => $this->entityFetchingService->find('upload', $uploadId),
            'selectedOperators' => $selectedOperators,
            'trainingRecords'   => $trainingRecords,
            'untrainedOperators' => $untrainedOperators,
            'operatorsByTrainer' => $operatorsByTrainer,
            'inTrainingOperatorsByTrainer' => $inTrainingOperatorsByTrainer,
        ]);
    }




    /**
     * Processes the training record form submission.
     *
     * This method handles the form submission for creating or updating training records.
     * It attempts to process the submitted data through the training record service and
     * redirects back to the training records display page regardless of success or failure.
     * Any exceptions during processing are logged but do not interrupt the user flow.
     *
     * @param int $uploadId The ID of the upload associated with the training records
     * @param Request $request The HTTP request containing the form data
     * @param int|null $teamId The ID of the team filter to maintain after processing
     * @param int|null $uapId The ID of the UAP filter to maintain after processing
     *
     * @return Response A redirect response to the training records display page with the same filters
     */
    #[Route('/operator/trainingRecord/form/{uploadId}/{teamId}/{uapId}', name: 'app_training_record_form')]
    public function trainingRecordFormManagement(int $uploadId, Request $request, ?int $teamId = null, ?int $uapId = null): Response
    {
        try {
            $this->trainingRecordService->trainingRecordTreatment($request);
        } catch (\Exception $e) {
            $this->logger->error('error during training record treatment', [$e]);
        } finally {
            return $this->redirectToRoute('app_render_training_records', [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }
    }
}
