<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\TrainingRecords;
use App\Entity\Upload;





class TrainingRecordService extends AbstractController
{
    protected $logger;
    protected $em;



    public function __construct(
        LoggerInterface                 $logger,
        EntityManagerInterface          $em

    ) {
        $this->logger                = $logger;
        $this->em                    = $em;
    }

    public function updateTrainingRecord(Upload $upload)
    {
        $trainingRecords = $upload->getTrainingRecords();
        $trained = false;
        foreach ($trainingRecords as $trainingRecord) {
            $this->logger->info('TrainingRecordService: updateTrainingRecord: trainingRecord: ' . $trainingRecord->getId());
            $trainingRecord->setTrained($trained);
            $this->em->persist($trainingRecord);
            $this->em->flush($trainingRecord);
        }
        return;
    }
}
