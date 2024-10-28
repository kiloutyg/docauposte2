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



    public function __construct(
        LoggerInterface             $logger,
        EntityManagerInterface      $em,
        SettingsRepository          $settingsRepository
    ){

        $this->logger = $logger;
        $this->em = $em;
        $this->settingsRepository = $settingsRepository;
    }

    // This function is responsible for getting the settings from the database and creating a form
    public function getSettingsFrom(): FormInterface
    {

        $settingsEntity = $this->settingsRepository->findOneBy(['id' => 1]);

        if ($settingsEntity === null) {
            $settingsEntity = new Settings();
        }

        $settingsForm = $this->createForm(SettingsType::class, $settingsEntity);

        return $settingsForm;
    }

    // This function is responsible for getting all the settings from the database
    protected function getSettings(): array
    {
        return $this->settingsRepository->findAll();
    }


    // This function is responsible for updating the settings in the database
    public function updateSettings(Request $request): void
    {        
        // $this->logger->info('Update settings service', [
        //     'request' => $request->request->all()
        // ]);

        $settingsEntity = $this->settingsRepository->findOneBy(['id' => 1]);

        if ($settingsEntity === null) {
            $settingsEntity = new Settings();
        }

        $settingsForm = $this->createForm(SettingsType::class, $settingsEntity);

        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $settingsEntity = $settingsForm->getData();
        }

        // $this->logger->info('Settings updated', [
        //     'settingsEntity' => $settingsEntity
        // ]);

        $this->em->persist($settingsEntity);
        $this->em->flush();
    }
}
