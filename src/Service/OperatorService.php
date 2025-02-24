<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

// use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Cache\CacheInterface;

// use App\Repository\UapRepository;
// use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;

use App\Service\EntityDeletionService;

class OperatorService extends AbstractController
{
    // private   $logger;
    private   $projectDir;
    private   $em;
    private   $cache;

    private   $operatorRepository;
    // private   $uapRepository;
    // private   $teamRepository;

    private     $entityDeletionService;

    public function __construct(
        // LoggerInterface         $logger,
        ParameterBagInterface   $params,
        EntityManagerInterface  $em,
        CacheInterface          $cache,


        OperatorRepository      $operatorRepository,
        // UapRepository           $uapRepository,
        // TeamRepository          $teamRepository,

        EntityDeletionService   $entityDeletionService

    ) {
        // $this->logger                   = $logger;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->em                       = $em;
        $this->cache                    = $cache;

        $this->operatorRepository       = $operatorRepository;
        // $this->uapRepository            = $uapRepository;
        // $this->teamRepository           = $teamRepository;

        $this->entityDeletionService    = $entityDeletionService;
    }


    public function operatorCheckForAutoDelete()
    {
        // $this->logger->info('Checking from operatorCheckForAutoDelete()');

        $today = new \DateTime();
        $fileName = 'checked_for_unactive_operator.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;


        // if ($today->format('d') % 4 == 0 && (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false)) {
        if (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false) {

            $return = false;

            $inActiveOperators = $this->operatorRepository->findOperatorWithNoRecentTraining();
            // $this->logger->info('Inactive operators: ' . json_encode($inActiveOperators));
            if (count($inActiveOperators) > 0) {
                foreach ($inActiveOperators as $operator) {
                    $operator->setInactiveSince($today);
                    $this->em->persist($operator);
                };
                $this->em->flush();
                $return = true;
            }


            $operatorSetToBeDeleted = $this->operatorRepository->findInActiveOperators();
            // $this->logger->info('Inactive operatorSetToBeDeleted: ' . json_encode($operatorSetToBeDeleted));
            if (count($operatorSetToBeDeleted) > 0) {
                foreach ($operatorSetToBeDeleted as $operator) {
                    $operator->setTobedeleted($today);
                    $this->em->persist($operator);
                };
                $this->em->flush();
                $return = true;
            }


            $toBeDeletedOperatorsIds = $this->operatorRepository->findOperatorToBeDeleted();
            // $this->logger->info('Inactive toBeDeletedOperatorsIds: ' . json_encode($toBeDeletedOperatorsIds));
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

            $this->cache->delete('operators_list');

            $countArray = [
                'findDeactivatedOperators' => count($this->operatorRepository->findDeactivatedOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
            return $countArray;
        }
    }
}
