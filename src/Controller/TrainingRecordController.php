<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;

use App\Controller\OperatorController;


class TrainingRecordController extends OperatorController
{

    // Methods to delete a weeks old training record maximum
    #[Route('/training-record/delete-weeks-old/{uploadId}/{teamId}/{uapId}/{trainingRecordId}', name: 'app_training_record_delete_weeks_old')]
    public function deleteWeeksOldTrainingRecords(int $trainingRecordId, int $uploadId, int $teamId, int $uapId): Response
    {

        $this->logger->info('TrainingRecordController: deleteWeeksOldTrainingRecords', ['trainingRecordId' => $trainingRecordId]);
        if ($this->authChecker->isGranted('ROLE_MANAGER')) {
            $response = $this->trainingRecordService->deleteWeeksOldTrainingRecords($trainingRecordId);
        } else {
            $this->addFlash(type: 'error', message: 'You are not authorized to delete training records');
            return $this->redirectToRoute(route: 'app_render_training_records', parameters: [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);
        }

        if ($response) {
            $this->addFlash(type: 'success', message: 'Training record deleted');
            return $this->redirectToRoute(route: 'app_render_training_records', parameters: [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);       
        } else {
            $this->addFlash(type: 'error', message: 'Error deleting training record');
            return $this->redirectToRoute(route: 'app_render_training_records', parameters: [
                'uploadId' => $uploadId,
                'teamId' => $teamId,
                'uapId' => $uapId,
            ]);        
        }
    }
}