<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\FormInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Settings;

use App\Repository\SettingsRepository;

use App\Form\SettingsType;

class SettingsService extends AbstractController
{
    protected $logger;
    protected $em;
    protected $settingsRepository;
    private $settings;
    private $incidentAutoDisplayTimerInSeconds;

    public function __construct(
        LoggerInterface             $logger,
        EntityManagerInterface      $em,
        SettingsRepository          $settingsRepository
    ) {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->settingsRepository   = $settingsRepository;
    }





    // This function is responsible for getting the settings from the database and creating a form
    public function getSettingsForm(): FormInterface
    {
        $settingsEntity = $this->getSettings();

        return $this->createForm(SettingsType::class, $settingsEntity);
    }



    // This function is responsible for getting all the settings from the database
    public function getSettings(): Settings
    {
        if (null === $this->settings) {
            // Assuming getSettings returns an associative array or an object with your settings
            $this->settings = $this->settingsRepository->getSettings();
        }
        if (!$this->settings) {
            $settingsEntity = new Settings();
            $this->em->persist($settingsEntity);
            $this->em->flush();
            $this->settings = $settingsEntity;
        }

        return $this->settings;
    }



    // This function is responsible for updating the settings in the database
    public function updateSettings(Request $request): Response
    {
        $settingsEntity = $this->getSettings();

        $settingsForm = $this->createForm(SettingsType::class, $settingsEntity);

        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $settingsEntity = $settingsForm->getData();
        }

        $response = '';

        try {
            $this->em->persist($settingsEntity);
            $this->em->flush();
            $response = true;
        } catch (\Exception $e) {
            $this->logger->error('Error updating settings', [
                'error' => $e->getMessage()
            ]);
            $response = $e->getMessage();
        }
        return new Response($response);
    }



    public function getIncidentAutoDisplayTimerInSeconds()
    {
        if ($this->incidentAutoDisplayTimerInSeconds === null) {
            $this->incidentAutoDisplayTimerInSeconds = $this->settingsRepository->getIncidentAutoDisplayTimerInSeconds();
        }
        return $this->incidentAutoDisplayTimerInSeconds;
    }


    public function getCurrentCodeOpeRegexPattern(): string
    {
        return $this->getSettings()->getOperatorCodeRegex();
    }
}
