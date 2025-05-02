<?php

namespace App\Service;

use App\Entity\Trainer;
use App\Entity\Operator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrainerService extends AbstractController
{
    private $em;
    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }



    /**
     * Handle trainer status updates for an operator
     */
    public function handleTrainerStatus(bool $isTrainer, Operator $operator): void
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
}
