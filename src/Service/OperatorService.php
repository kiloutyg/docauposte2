<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\UAP;
use App\Entity\Team;

use App\Repository\UapRepository;
use App\Repository\TeamRepository;
use App\Repository\OperatorRepository;

use App\Service\EntityDeletionService;

class OperatorService extends AbstractController
{
    protected   $logger;
    protected   $operatorRepository;
    protected   $uapRepository;
    protected   $teamRepository;
    protected   $projectDir;
    protected   $em;
    private     $entityDeletionService;


    public function __construct(
        LoggerInterface         $logger,
        OperatorRepository      $operatorRepository,
        UapRepository           $uapRepository,
        TeamRepository          $teamRepository,
        ParameterBagInterface   $params,
        EntityManagerInterface  $em,
        EntityDeletionService   $entityDeletionService

    ) {
        $this->logger                   = $logger;
        $this->operatorRepository       = $operatorRepository;
        $this->uapRepository            = $uapRepository;
        $this->teamRepository           = $teamRepository;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->em                       = $em;
        $this->entityDeletionService    = $entityDeletionService;
    }


    public function operatorCheckForAutoDelete()
    {
        $this->logger->info('Checking from operatorCheckForAutoDelete()');

        $today = new \DateTime();
        $fileName = 'checked_for_unactive_operator.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;


        // if ($today->format('d') % 4 == 0 && (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false)) {
        if (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false) {

            $return = false;

            $inActiveOperators = $this->operatorRepository->findOperatorWithNoRecentTraining();

            $this->logger->info('Inactive operators: ' . json_encode($inActiveOperators));
            if (count($inActiveOperators) > 0) {
                foreach ($inActiveOperators as $operator) {
                    $operator->setTobedeleted($today);
                    $this->em->persist($operator);
                };
                $this->em->flush();
                $return = true;
            }


            $toBeDeletedOperatorsIds = $this->operatorRepository->findOperatorToBeDeleted();
            $this->logger->info('To be deleted operators: ' . json_encode($toBeDeletedOperatorsIds));

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


            $countArray = [
                'inActiveOperators' => count($this->operatorRepository->findInActiveOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
            return $countArray;
        }
    }

    public function deleteUAP(UAP $uap)
    {
        $unDefinedUap = $this->uapRepository->findOneBy(['name' => 'IDEFINI']);
        $uapOperators = $uap->getOperator();

        foreach ($uapOperators as $operator) {
            $operator->setUap($unDefinedUap);
            $this->em->persist($operator);
        }
        $this->em->flush();
        return true;
    }

    public function deleteTeam(Team $team)
    {
        $unDefinedTeam = $this->teamRepository->findOneBy(['name' => 'IDEFINI']);
        $teamOperators = $team->getOperator();

        foreach ($teamOperators as $operator) {
            $operator->setTeam($unDefinedTeam);
            $this->em->persist($operator);
        }
        $this->em->flush();
        return true;
    }
}
