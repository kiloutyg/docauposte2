<?php

namespace App\Service\Iluo;

use App\Entity\Operator;
use App\Entity\Iluo;
use App\Entity\Workstation;
use App\Entity\IluoChecklist;

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




    public function iluoCheckUpdate()
    {
        $allOperators = $this->entityFetchingService->getOperators();
        foreach ($allOperators as $operator) {
            if (empty($operator->getIluos())) {
                $this->initialIluoCreation(operator: $operator);
            }
        }
    }

    private function initialIluoCreation(Operator $operator): void
    {
        // $this->logger->debug(message: 'iluoService::initialIluoCreation', context: ['operatorId' => $operator->getId()]);
        $operatorUaps = $operator->getUaps();

        if (empty($operatorUaps)) {
            $this->logger->error(message: 'iluoService::initialIluoCreation - No UAPs found for operator', context: ['operatorId' => $operator->getId()]);
            return;
        } else {
            $this->logger->debug(message: 'iluoService::initialIluoCreation - Creating initial ILUO for operator', context: ['operatorId' => $operator->getId()]);
            $workstations = [];
            foreach ($operatorUaps as $uap) {
                $workstations = $uap->getWorkstations();
                if (empty($workstations)) {
                    $this->logger->error(message: 'iluoService::initialIluoCreation - No workstations found for UAP', context: ['uapId' => $uap->getId()]);
                    continue;
                }
                foreach ($workstations as $workstation) {
                    $this->logger->debug(message: 'iluoService::initialIluoCreation - Creating initial ILUO for workstation', context: ['workstationId' => $workstation->getId()]);
                    $this->iluoCreation($operator, $workstation);
                }
            }
        }
    }

    private function iluoCreation(Operator $operator, Workstation $workstation)
    {
        $upload = $workstation->getUpload();
        $trainingRecord = $this->entityFetchingService->findBy(entityType: 'trainingRecords', criteria: [
            'upload' => $upload,
            'operator' => $operator
        ]);

        if (empty($trainingRecord)) {
            $this->logger->error(message: 'iluoService::initialIluoCreation - No training record found for upload', context: [
                'uploadId' => $upload->getId(),
                'operatorId' => $operator->getId()
            ]);
            return;
        } elseif (!$trainingRecord->isTrained()) {
            $this->logger->error(message: 'iluoService::initialIluoCreation - Operator not trained for upload', context: [
                'uploadId' => $upload->getId(),
                'operatorId' => $operator->getId()
            ]);
            return;
        } else {

            $this->logger->debug(message: 'iluoService::initialIluoCreation - Creating ILUO for operator', context: [
                'operatorId' => $operator->getId(),
                'workstationId' => $workstation->getId()
            ]);

            $iluo = new Iluo();
            $iluo->setOperator(operator: $operator);
            $iluo->setWorkstation(workstation: $workstation);
            $iluo->setProduct(product: $workstation->getProduct());
            $iluo->setStartDate(startDate: new \DateTime());
            $this->em->persist(object: $iluo);

            $this->logger->info(message: 'iluoService::initialIluoCreation - ILUO created successfully', context: [
                'iluoId' => $iluo->getId(),
                'operatorId' => $operator->getId()
            ]);
        }
    }

    public function iluoCheckListCreation(Iluo $iluo){

    }
}
