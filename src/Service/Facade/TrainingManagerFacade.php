<?php

namespace App\Service\Facade;

use App\Entity\Operator;

use App\Service\Operator\TeamService;
use App\Service\Operator\TrainerService;
use App\Service\Operator\UapService;


class TrainingManagerFacade
{
    private     $teamService;
    private     $trainerService;
    private     $uapService;

    public function __construct(
        TeamService                     $teamService,
        TrainerService                  $trainerService,
        UapService                      $uapService
    ) {
        $this->teamService              = $teamService;
        $this->trainerService           = $trainerService;
        $this->uapService               = $uapService;
    }


    public function handleTrainerStatus(bool $isTrainer, Operator $operator)
    {
        $this->trainerService->handleTrainerStatus($isTrainer, $operator);
    }

    public function teamInitialization()
    {
        $this->teamService->teamInitialization();
    }

    public function updateOperatorUaps(array $newUapsArray, Operator $operator)
    {
        $this->uapService->updateOperatorUaps($newUapsArray, $operator);
    }

    public function uapInitialization()
    {
        $this->uapService->uapInitialization();
    }

    public function trainerInactivityCheck(Operator $operator): bool
    {
        return $this->trainerService->trainerInactivityCheck(operator: $operator);
    }
}
