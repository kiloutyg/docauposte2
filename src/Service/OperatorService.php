<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;


use App\Repository\OperatorRepository;

use App\Service\EntityDeletionService;

class OperatorService extends AbstractController
{
    protected $logger;
    protected $operatorRepository;
    protected $projectDir;
    protected $em;
    private $entityDeletionService;

    public function __construct(
        LoggerInterface $logger,
        OperatorRepository $operatorRepository,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        EntityDeletionService $entityDeletionService
    ) {
        $this->logger               = $logger;
        $this->operatorRepository   = $operatorRepository;
        $this->projectDir           = $params->get('kernel.project_dir');
        $this->em                   = $em;
        $this->entityDeletionService = $entityDeletionService;
    }


    public function operatorCheckForAutoDelete()
    {
        $today = new \DateTime();
        $fileName = 'email_sent.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;


        if ($today->format('d') % 4 == 0 && (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false)) {
            $return = false;

            $unActiveOperators = $this->operatorRepository->findOperatorWithNoRecentTraining();
            if (count($unActiveOperators) > 0) {
                foreach ($unActiveOperators as $operator) {
                    $operator->setTobedeleted($today);
                    $this->em->persist($operator);
                };
                $this->em->flush();
                $return = true;
            }

            // $toBeDeletedOperators = $this->operatorRepository->findOperatorToBeDeleted();
            // if (count($toBeDeletedOperators) > 0) {
            //     foreach ($toBeDeletedOperators as $operator) {
            //         $this->entityDeletionService->deleteEntity('operator', $operator->getId());
            //     };
            //     $this->em->flush();
            //     $return = true;
            // }
            $toBeDeletedOperatorsIds = $this->operatorRepository->findOperatorToBeDeleted();
            if (count($toBeDeletedOperatorsIds) > 0) {
                foreach ($toBeDeletedOperatorsIds as $operatorId) {
                    $this->entityDeletionService->deleteEntity('operator', $operatorId);
                };
                $this->em->flush();
                $return = true;
            }

            if ($return) {
                file_put_contents($filePath, $today->format('Y-m-d'));
            }
        }
    }
}
