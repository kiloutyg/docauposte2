<?php

namespace App\Service\Iluo;

use App\Entity\Operator;
use App\Entity\Iluo;
use App\Entity\IluoChecklist;
use App\Entity\Workstation;

use App\Service\EntityFetchingService;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Psr\Log\LoggerInterface;

class IluoChecklistService extends AbstractController
{

    private $logger;

    private $entityFetchingService;

    private $em;

    public function __construct(
        LoggerInterface                 $logger,
        EntityFetchingService           $entityFetchingService,
        EntityManagerInterface          $em
    ) {
        $this->logger                   = $logger;
        $this->entityFetchingService    = $entityFetchingService;
        $this->em                       = $em;
    }




    /**
     * Iterates through all operators to create initial ILUO records.
     * This function fetches all operators and, for each one that already has associated ILUOs,
     * it triggers the creation process for potentially new ILUOs based on their workstations.
     *
     * @return int The total number of new ILUOs created.
     */
    public function checkIluoUpdates()
    {
        $count = 0;
        $allOperators = $this->entityFetchingService->getOperators();

        foreach ($allOperators as $operator) {
            if (!empty($operator->getIluos())) {
                $count += $this->initialIluoCreation(operator: $operator);
            }
        }
        $this->em->flush();

        return $count;
    }


    
    /**
     * Checks and updates ILUO records for a specific operator.
     *
     * This function retrieves the operator entity associated with the given operator ID.
     * If no operator is found, an error message is logged and the function returns 0.
     * Otherwise, it calls the `initialIluoCreation` method to create new ILUO records for the operator.
     * The changes are then committed to the database using flush().
     *
     * @param int $operatorId The ID of the operator for which to check and update ILUO records.
     * @return int The total number of ILUO records created for the specified operator.
     */
    public function checkIluoUpdatesBySpecificOperator(int $operatorId)
    {

        $count = 0;
        $operator = $this->entityFetchingService->find(entityType: 'operator', entityId: $operatorId);
        if (empty($operator)) {
            $this->logger->error(
                message: 'iluoChecklistService::checkIluoUpdatesBySpecificOperator - No operator found for the given ID',
                context: ['operatorId' => $operatorId]
            );
            return $count;
        }
        $count += $this->initialIluoCreation(operator: $operator);
        $this->em->flush();

        return $count;
    }


    /**
     * Checks and updates ILUO records for a specific upload.
     *
     * This function iterates through all operators associated with the UAP of the workstation
     * associated with the given upload ID. For each operator, it creates a new ILUO record if
     * one does not already exist. The function also ensures that the operator has a valid and
     * completed training record for the upload associated with the workstation.
     *
     * @param int $uploadId The ID of the upload for which to check and update ILUO records.
     * @return int The number of new ILUO records created for the specified upload.
     */
    public function checkIluoUpdatesBySpecificUpload(int $uploadId)
    {
        $count = 0;

        // Fetch the workstation associated with the given upload ID
        $workstation = $this->entityFetchingService->findOneBy(
            entityType: 'workstation',
            criteria: ['upload' => $uploadId]
        );

        // Ensure there is a valid workstation and UAP associated with the upload
        if (empty($workstation) || empty($workstation->getUap())) {
            $this->logger->error(
                message: 'iluoChecklistService::checkIluoUpdatesBySpecificUpload - No workstation or UAP found for the given upload',
                context: ['uploadId' => $uploadId]
            );
            return $count;
        }

        // Iterate through all operators associated with the UAP of the workstation
        $allUapOperators = $workstation->getUap()->getOperators();
        foreach ($allUapOperators as $operator) {
            if ($this->iluoCreation(operator: $operator, workstation: $workstation)) {
                $count++;
            }
        }

        $this->em->flush();

        return $count;
    }


    /**
     * Creates initial ILUO records for a specific operator.
     * This function iterates through the UAPs and workstations associated with the given operator
     * and triggers the creation of an ILUO for each valid operator-workstation combination.
     *
     * @param Operator $operator The operator for whom to create the ILUOs.
     * @return int The number of new ILUOs created for the specified operator.
     */
    private function initialIluoCreation(Operator $operator): int
    {
        $operatorUaps = $operator->getUaps();

        if (empty($operatorUaps)) {

            $this->logger->error(
                message: 'iluoChecklistService::initialIluoCreation - No UAPs found for operator',
                context: ['operatorId' => $operator->getId()]
            );

            return 0;
        }

        $this->logger->debug(
            message: 'iluoChecklistService::initialIluoCreation - Creating initial ILUO for operator',
            context: ['operatorId' => $operator->getId()]
        );

        $workstations = [];
        $count = 0;

        foreach ($operatorUaps as $uap) {

            $workstations = $uap->getWorkstations();

            if (empty($workstations)) {

                $this->logger->error(
                    message: 'iluoChecklistService::initialIluoCreation - No workstations found for UAP',
                    context: ['uapId' => $uap->getId()]
                );
            }

            foreach ($workstations as $workstation) {

                $this->logger->debug(
                    message: 'iluoChecklistService::initialIluoCreation - Creating initial ILUO for workstation',
                    context: ['workstationId' => $workstation->getId()]
                );

                if ($this->iluoCreation(operator: $operator, workstation: $workstation)) {
                    $count++;
                }
            }
        }
        return $count;
    }


    /**
     * Iterates through all workstations to create ILUO records for their associated operators.
     * This function ensures that an ILUO exists for every valid combination of an operator
     * and a workstation within the same UAP.
     *
     * @return int The total number of new ILUOs created.
     */
    public function checkIluoUpdatesByAllWorkstations()
    {
        $count = 0;
        $allWorkstations = $this->entityFetchingService->getWorkstations();
        foreach ($allWorkstations as $workstation) {
            if (!empty($workstation->getUap())) {
                $allUapOperators = $workstation->getUap()->getOperators();
                foreach ($allUapOperators as $operator) {
                    if ($this->iluoCreation(operator: $operator, workstation: $workstation)) {
                        $count++;
                    }
                }
            }
        }
        $this->em->flush();

        return $count;
    }





    /**
     * Creates a new ILUO record for a specific operator and workstation combination.
     *
     * This function first checks if an ILUO already exists for the given operator, workstation,
     * and associated product. If it does, the function returns false. It then verifies that the
     * operator has a valid and completed training record for the upload associated with the
     * workstation. If the training record is missing or incomplete, the function also returns false.
     * If all checks pass, a new Iluo entity is created, persisted, and a corresponding
     * checklist is generated.
     *
     * @param Operator $operator The operator entity for whom the ILUO is to be created.
     * @param Workstation $workstation The workstation entity associated with the ILUO.
     * @return bool Returns true if a new ILUO was successfully created, false otherwise.
     */
    private function iluoCreation(Operator $operator, Workstation $workstation)
    {

        $this->logger->debug(
            message: 'iluoChecklistService::iluoCreation - Creating ILUO for operator',
            context: [
                'operatorId' => $operator->getId(),
                'workstationId' => $workstation->getId()
            ]
        );


        // Check if an ILUO already exists for this combination
        $existingIluo = $this->em->getRepository(Iluo::class)->findOneBy([
            'operator' => $operator,
            'workstation' => $workstation,
            'product' => $workstation->getProducts(),
        ]);

        if ($existingIluo) {
            $this->logger->debug(
                'iluoChecklistService::iluoCreation - ILUO already exists, skipping creation.',
                [
                    'iluoId' => $existingIluo->getId(),
                    'operatorId' => $operator->getId(),
                    'workstationId' => $workstation->getId(),
                    'productId' => $workstation->getProducts()->getId(),
                ]
            );
            return false;
        }


        $upload = $workstation->getUpload();
        $trainingRecord = $this->entityFetchingService->findOneBy(
            entityType: 'trainingRecord',
            criteria: [
                'upload' => $upload,
                'operator' => $operator
            ]
        );

        $this->logger->debug(
            message: 'iluoChecklistService::iluoCreation - Checking training record for operator and upload',
            context: [
                'trainingRecord' => $trainingRecord
            ]
        );
        if (empty($trainingRecord) || !$trainingRecord->isTrained()) {
            $this->logger->error(
                message: 'iluoChecklistService::iluoCreation - No training record found for upload or Operator not trained for upload',
                context: [
                    'uploadId' => $upload->getId(),
                    'operatorId' => $operator->getId(),
                    'isTrained' => $trainingRecord ? 'true' : 'false',
                ]
            );
            return false;
        }


        // Create a new ILUO if it doesn't exist
        $iluo = new Iluo();
        $iluo->setOperator(operator: $operator);
        $iluo->setWorkstation(workstation: $workstation);
        $iluo->setProduct(product: $workstation->getProducts());
        $iluo->setStartDate(startDate: new \DateTime());

        $this->em->persist(object: $iluo);

        $this->logger->debug(
            message: 'iluoChecklistService::iluoCreation - ILUO created successfully',
            context: [
                'iluoId' => $iluo->getId(),
                'operatorId' => $operator->getId()
            ]
        );

        $this->iluoCheckListCreation($iluo);
        return true;
    }





    /**
     * Creates an ILUO checklist for a given ILUO entity.
     *
     * This function initializes a new IluoChecklist, associates it with the provided Iluo,
     * and then populates it with relevant steps by calling separate methods for workstation-specific,
     * upload-specific, and other general steps.
     *
     * @param Iluo $iluo The ILUO entity for which to create the checklist.
     * @return void
     */
    public function iluoCheckListCreation(Iluo $iluo)
    {
        $this->logger->debug(
            message: 'iluoChecklistService::iluoCheckListCreation',
            context: ['iluoId' => $iluo->getId()]
        );

        $iluoChecklist = new IluoChecklist();
        $iluoChecklist->setIluo(iluo: $iluo);

        $this->em->persist(object: $iluoChecklist);

        $this->logger->debug(
            message: 'iluoChecklistService::iluoCheckListCreation - Checklist created successfully',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );

        $this->iluoChecklistWorkstationStepsCreation(iluoChecklist: $iluoChecklist);
        $this->iluoChecklistSpecificUploadStepsCreation(iluoChecklist: $iluoChecklist);
        $this->iluoChecklistOtherStepsCreation(iluoChecklist: $iluoChecklist);
    }







    /**
     * Populates an ILUO checklist with steps related to workstation training materials.
     *
     * This function finds all training material types categorized as 'Workstation' and adds
     * their associated steps to the provided IluoChecklist. These steps are specific to the
     * workstation linked to the ILUO.
     *
     * @param IluoChecklist $iluoChecklist The ILUO checklist entity to which the workstation-specific steps will be added.
     * @return void
     */
    public function iluoChecklistWorkstationStepsCreation(IluoChecklist $iluoChecklist)
    {
        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistWorkstationStepsCreation',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );

        $trainingMaterialTypeWorkstations = $this->entityFetchingService->findBy(
            entityType: 'trainingMaterialType',
            criteria: ['category' => 'Workstation']
        );

        if (!empty($trainingMaterialTypeWorkstations)) {

            foreach ($trainingMaterialTypeWorkstations as $trainingMaterialTypeWorkstation) {

                $this->logger->debug(
                    message: 'iluoChecklistService::iluoChecklistWorkstationStepsCreation - Processing workstation training material type',
                    context: ['trainingMaterialTypeId' => $trainingMaterialTypeWorkstation]
                );

                $stepsWorkstationRelated =  $trainingMaterialTypeWorkstation->getSteps();

                foreach ($stepsWorkstationRelated as $step) {
                    $iluoChecklist->addStep(step: $step);
                }

                $this->logger->debug(
                    message: 'iluoChecklistService::iluoChecklistWorkstationStepsCreation - Checklist steps created successfully',
                    context: ['iluoChecklistId' => $iluoChecklist->getId()]
                );
            }
            return;
        }
        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistWorkstationStepsCreation - No steps found for the workstation training material types',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );
    }





    /**
     * Populates an ILUO checklist with steps related to specific upload training materials.
     *
     * This function finds all training material types categorized as 'Specific Upload' and adds
     * their associated steps to the provided IluoChecklist, but only if the operator associated
     * with the ILUO has a valid training record for the specific upload and is marked as trained.
     *
     * @param IluoChecklist $iluoChecklist The ILUO checklist entity to which the specific upload steps will be added.
     * @return void
     */
    public function iluoChecklistSpecificUploadStepsCreation(IluoChecklist $iluoChecklist)
    {
        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistSpecificUploadStepsCreation',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );

        $trainingMaterialTypepecificUploads = $this->entityFetchingService->findBy(
            entityType: 'trainingMaterialType',
            criteria: ['category' => 'Specific Upload']
        );

        if (!empty($trainingMaterialTypepecificUploads)) {

            foreach ($trainingMaterialTypepecificUploads as $trainingMaterialTypepecificUpload) {

                $trainingRecord = $this->entityFetchingService->findOneBy(
                    entityType: 'trainingRecord',
                    criteria: [
                        'operator' => $iluoChecklist->getIluo()->getOperator(),
                        'upload' => $trainingMaterialTypepecificUpload->getUpload(),
                        'trained' => true
                    ]
                );

                if (!empty($trainingRecord)) {

                    $stepsSpecificUploadRelated = $trainingMaterialTypepecificUpload->getSteps();

                    foreach ($stepsSpecificUploadRelated as $step) {
                        $iluoChecklist->addStep(step: $step);
                    }

                    $this->logger->debug(
                        message: 'iluoChecklistService::iluoChecklistSpecificUploadStepsCreation - Checklist steps created successfully',
                        context: ['iluoChecklistId' => $iluoChecklist->getId()]
                    );
                }
            }

            return;
        }
        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistSpecificUploadStepsCreation - No steps found for specific upload training material types',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );
    }





    /**
     * Populates an ILUO checklist with steps related to general training materials.
     *
     * This function finds all training material types categorized as 'Other' and adds
     * their associated steps to the provided IluoChecklist. These are general steps
     * that apply to all operators regardless of their specific workstation or upload training.
     *
     * @param IluoChecklist $iluoChecklist The ILUO checklist entity to which the general steps will be added.
     * @return void
     */
    public function iluoChecklistOtherStepsCreation(IluoChecklist $iluoChecklist)
    {
        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistOtherStepsCreation',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );

        $trainingMaterialTypeOthers = $this->entityFetchingService->findBy(
            entityType: 'trainingMaterialType',
            criteria: ['category' => 'Other']
        );
        if (!empty($trainingMaterialTypeOthers)) {
            foreach ($trainingMaterialTypeOthers as $trainingMaterialTypeOther) {

                $stepsOtherRelated = $trainingMaterialTypeOther->getSteps();

                foreach ($stepsOtherRelated as $step) {
                    $iluoChecklist->addStep(step: $step);
                }

                $this->logger->debug(
                    message: 'iluoChecklistService::iluoChecklistOtherStepsCreation - Checklist steps created successfully',
                    context: ['iluoChecklistId' => $iluoChecklist->getId()]
                );
            }
            return;
        }

        $this->logger->debug(
            message: 'iluoChecklistService::iluoChecklistOtherStepsCreation - No steps found for the other training material types',
            context: ['iluoChecklistId' => $iluoChecklist->getId()]
        );
    }



    /**
     * Deletes all ILUO records from the database.
     *
     * This function retrieves all existing ILUO entities and removes them from the database.
     * It performs a bulk deletion operation and logs the number of deleted records for
     * auditing purposes. The deletion is committed to the database using flush().
     *
     * @return int The total number of ILUO records that were deleted from the database.
     */
    public function deleteAllIluos()
    {
        $allIluos = $this->entityFetchingService->getIluos();
        $count = 0;
        foreach ($allIluos as $iluo) {
            $this->em->remove(object: $iluo);
            $count++;
        }
        $this->em->flush();

        $this->logger->debug(
            message: 'iluoChecklistService::deleteAllIluos - All ILUOs deleted successfully',
            context: ['deletedCount' => $count]
        );
        return $count;
    }
}
