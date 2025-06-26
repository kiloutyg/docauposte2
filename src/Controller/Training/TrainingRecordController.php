<?php

namespace App\Controller\Training;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\Operator\TrainingRecordService;

/**
 * TrainingRecordController
 *
 * This controller manages operations related to training records in the application.
 * It provides functionality for deleting outdated training records and enforces
 * role-based access control to ensure only authorized users can perform these operations.
 */
class TrainingRecordController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface Authorization checker for role-based access control
     */
    private $authChecker;

    /**
     * @var TrainingRecordService Service for training record operations
     */
    private $trainingRecordService;

    /**
     * Constructor for TrainingRecordController
     *
     * Initializes the authorization checker and training record service
     * required for managing training record operations.
     *
     * @param AuthorizationCheckerInterface $authChecker Service for checking user permissions
     * @param TrainingRecordService $trainingRecordService Service for training record operations
     */
    public function __construct(
        AuthorizationCheckerInterface $authChecker,
        TrainingRecordService $trainingRecordService,
    ) {
        $this->authChecker = $authChecker;
        $this->trainingRecordService = $trainingRecordService;
    }

    /**
     * Deletes outdated training records
     *
     * This method handles the deletion of training records that are considered outdated.
     * It enforces role-based access control to ensure only managers can delete records.
     * After deletion, it redirects back to the training records view with an appropriate
     * flash message indicating success or failure.
     *
     * @param int $trainingRecordId The ID of the training record to delete
     * @param int $uploadId The ID of the upload associated with the training record
     * @param int $teamId The ID of the team associated with the training record
     * @param int $uapId The ID of the UAP (Unit Assembly Process) associated with the training record
     * @return Response A redirect response to the training records view
     */
    #[Route('/training-record/delete-weeks-old/{uploadId}/{teamId}/{uapId}/{trainingRecordId}', name: 'app_training_record_delete_weeks_old')]
    public function deleteWeeksOldTrainingRecords(int $trainingRecordId, int $uploadId, int $teamId, int $uapId): Response
    {
        // Check if the user has manager role
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

        // Handle the response from the service
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
