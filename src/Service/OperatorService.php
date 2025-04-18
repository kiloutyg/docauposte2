<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\Cache\CacheInterface;

use App\Repository\OperatorRepository;
use App\Repository\UapRepository;

use App\Service\EntityDeletionService;

use App\Entity\Operator;
use App\Entity\Trainer;

class OperatorService extends AbstractController
{
    private   $logger;
    private   $projectDir;
    private   $em;
    private   $cache;

    private   $operatorRepository;
    private   $uapRepository;

    private     $entityDeletionService;

    public function __construct(
        LoggerInterface         $logger,
        ParameterBagInterface   $params,
        EntityManagerInterface  $em,
        CacheInterface          $cache,

        OperatorRepository      $operatorRepository,
        UapRepository           $uapRepository,

        EntityDeletionService   $entityDeletionService

    ) {
        $this->logger                   = $logger;
        $this->projectDir               = $params->get('kernel.project_dir');
        $this->em                       = $em;
        $this->cache                    = $cache;

        $this->operatorRepository       = $operatorRepository;
        $this->uapRepository            = $uapRepository;

        $this->entityDeletionService    = $entityDeletionService;
    }


    public function operatorCheckForAutoDelete()
    {

        $today = new \DateTime();
        $fileName = 'checked_for_unactive_operator.txt';
        $filePath = $this->projectDir . '/public/doc/' . $fileName;


        if (!file_exists($filePath) || strpos(file_get_contents($filePath), $today->format('Y-m-d')) === false) {

            $return = false;

            $inActiveOperators = $this->operatorRepository->findOperatorWithNoRecentTraining();
            if (count($inActiveOperators) > 0) {
                foreach ($inActiveOperators as $operator) {
                    $operator->setInactiveSince($today);
                    $this->em->persist($operator);
                }
                $this->em->flush();
                $return = true;
            }

            $operatorSetToBeDeleted = $this->operatorRepository->findInActiveOperators();

            if (count($operatorSetToBeDeleted) > 0) {
                foreach ($operatorSetToBeDeleted as $operator) {
                    $operator->setTobedeleted($today);
                    $this->em->persist($operator);
                }
                $this->em->flush();
                $return = true;
            }

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

            $this->cache->delete('operators_list');

            return [
                'findDeactivatedOperators' => count($this->operatorRepository->findDeactivatedOperators()),
                'toBeDeletedOperators' => count($toBeDeletedOperatorsIds)
            ];
        }
    }


    public function editOperatorService(Form $form, Operator $operator)
    {
        $this->handleTrainerStatus($form->get('isTrainer')->getData(), $operator);
        $this->reactivateOperatorIfNeeded($operator);
        $this->updateOperatorUaps($form->get('uaps')->getData()->toArray(), $operator);
        $this->em->flush();
        return true;
    }



    /**
     * Handle trainer status updates for an operator
     */
    private function handleTrainerStatus(bool $isTrainer, Operator $operator): void
    {
        $trainer = $operator->getTrainer();
        if ($isTrainer) {
            $this->promoteToTrainer($operator, $trainer);
        } else {
            $this->demoteFromTrainer($operator, $trainer);
        }
    }

    /**
     * Promote an operator to trainer status
     */
    private function promoteToTrainer(Operator $operator, ?Trainer $trainer): void
    {
        if ($trainer === null) {
            $trainer = new Trainer();
            $trainer->setOperator($operator);
            $operator->setTrainer($trainer);
        }

        $trainer->setDemoted(false);
        $this->em->persist($trainer);
    }

    /**
     * Demote an operator from trainer status
     */
    private function demoteFromTrainer(Operator $operator, ?Trainer $trainer): void
    {
        if ($trainer === null) {
            return;
        }

        if (!$trainer->getTrainingRecords()->isEmpty()) {
            $trainer->setDemoted(true);
            $this->em->persist($trainer);
        } else {
            $operator->setIsTrainer(false);
            $this->em->remove($trainer);
        }
    }

    /**
     * Reactivate an operator if they were marked for deletion
     */
    private function reactivateOperatorIfNeeded(Operator $operator): void
    {
        if ($operator->getTobedeleted() === null) {
            return;
        }

        $operator->setTobedeleted(null);
        $operator->setLasttraining(new \DateTime());
        $operator->setInactiveSince(null);
    }

    /**
     * Update the UAPs associated with an operator
     */
    private function updateOperatorUaps(array $newUapsArray, Operator $operator): void
    {
        if (empty($newUapsArray)) {
            return;
        }

        // Remove operator from all UAPs
        $allUaps = $this->uapRepository->findAll();
        foreach ($allUaps as $uap) {
            $uap->removeOperator($operator);
        }

        // Add operator to selected UAPs
        foreach ($newUapsArray as $newUap) {
            $newUap->addOperator($operator);
            $this->em->persist($newUap);
        }
    }


    /**
     * Process new operator entity
     */
    public function processNewOperator(Operator $newOperator, $form)
    {

        $trainerBool = $form->get('isTrainer')->getData();
        if ($trainerBool == true) {
            $trainer = new Trainer();
            $trainer->setOperator($newOperator);
            $trainer->setDemoted(false);
            $this->em->persist($trainer);
            $newOperator->setTrainer($trainer);
        } elseif ($trainerBool != true) {
            $trainer = $newOperator->getTrainer();
            $newOperator->setTrainer(null);
            if ($trainer != null) {
                $this->em->remove($trainer);
            }
        };
        $operator = $form->getData();
        $uaps = $operator->getUaps();
        foreach ($uaps as $uap) {
            $uap->addOperator($operator);
            $this->em->persist($uap);
        }
        $this->em->persist($operator);
        $this->em->flush();

        return $operator->getId();
    }
}
