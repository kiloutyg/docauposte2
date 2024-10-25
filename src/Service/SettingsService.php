<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Settings;

use App\Repository\SettingsRepository;


class SettingsService extends AbstractController
{
    protected $logger;
    protected $em;

    protected $settingsRepository;



    public function __construct(
        LoggerInterface             $logger,
        EntityManagerInterface      $em,
        SettingsRepository          $settingsRepository
    ){

        $this->logger = $logger;
        $this->em = $em;
        $this->settingsRepository = $settingsRepository;
    }

    protected function getSettings(): array
    {
        return $this->settingsRepository->findAll();
    }

    public function updateSettings(Request $request): void
{        
    $this->logger->info('Update settings service', [
        'request' => $request->request->all()
    ]);

    $settingsEntity = $this->settingsRepository->findOneBy(['id' => 1]);

    if ($settingsEntity === null) {
        $settingsEntity = new Settings();
    }

    $allRequest = $request->request->all();
    $settings = $allRequest['settings'] ?? [];

    $validatorNumber = $settings['ValidatorNumber'] ?? null;
    $training = $settings['Training'] ?? null;
    $autoDisplayIncident = $settings['AutoDisplayIncident'] ?? null;

    $autoDisplayIncidentTimerInHours = $settings['AutoDisplayIncidentTimer']['hour'] ?? 0;
    $autoDisplayIncidentTimerInMinutes = $settings['AutoDisplayIncidentTimer']['minute'] ?? 0;

    $autoDeleteOperatorDelayInMonths = $settings['AutoDeleteOperatorDelay'] ?? null;

    $autoDisplayIncidentTimer = new \DateTime();
    $autoDisplayIncidentTimer->setTime($autoDisplayIncidentTimerInHours, $autoDisplayIncidentTimerInMinutes, 0);

    $autoDeleteOperatorDelay = new \DateTime();
    $autoDeleteOperatorDelay->modify('-' . $autoDeleteOperatorDelayInMonths . ' months');

    $this->logger->info('Settings updated', [
        'validatorNumber' => $validatorNumber,
        'training' => $training,
        'autoDisplayIncident' => $autoDisplayIncident,
        'autoDisplayIncidentTimer' => $autoDisplayIncidentTimer,
        'autoDeleteOperatorDelay' => $autoDeleteOperatorDelay,
    ]);

    $settingsEntity->setValidatorNumber($validatorNumber);
    $settingsEntity->setTraining($training);
    $settingsEntity->setAutoDisplayIncident($autoDisplayIncident);
    $settingsEntity->setAutoDisplayIncidentTimer($autoDisplayIncidentTimer);
    $settingsEntity->setAutoDeleteOperatorDelay($autoDeleteOperatorDelay);
    
    $this->em->persist($settingsEntity);
    $this->em->flush();
}
}
