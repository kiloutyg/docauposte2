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
    #[Route('operator/traininglist/{uploadId}', name: 'app_training_list')]
    public function trainingList(int $uploadId): Response
    {
        $upload = $this->entityFetchingService->find('upload', $uploadId);
        $trainingRecords = $this->trainingRecordService->getOrderedTrainingRecordsByUpload($upload);

        return $this->render('services/operators/operatorTraining.html.twig', [
            'trainingRecords'   => $trainingRecords,
            'upload'            => $upload,
            'teams'             => $this->entityFetchingService->getTeams(),
            'uaps'              => $this->entityFetchingService->getUaps(),
            'operators'         => $this->entityFetchingService->getOperators(),
        ]);
    }




    // Route to handle the newOperator form submission
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




    #[Route('/operator/render-training-records/{uploadId}/{teamId}/{uapId}', name: 'app_render_training_records')]
    public function renderTrainingRecords(int $uploadId, ?int $teamId = null, ?int $uapId = null): Response
    {
        $selectedOperators = $this->entityFetchingService->findOperatorsByTeamAndUapId($teamId, $uapId);

        $trainingRecords = []; // Array of training records
        $unorderedTrainingRecords = []; // Array of unordered training records
        $untrainedOperators = []; // Array of untrained operators
        $operatorsByTrainer = []; // Array of operators grouped by trainer
        $inTrainingOperatorsByTrainer = []; // Array of operators in training grouped by trainer

        foreach ($selectedOperators as $operator) {
            $records = $this->entityFetchingService->findBy('trainingRecord', ['operator' => $operator, 'upload' => $uploadId]);
            $unorderedTrainingRecords = array_merge($trainingRecords, $records);

            $record = $records[0] ?? null;
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
