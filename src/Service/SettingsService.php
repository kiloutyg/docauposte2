<?php

namespace App\Service;

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

    protected function updateSettings(Settings $settings): void
    {
        $this->em->persist($settings);
        $this->em->flush();
    }
}
