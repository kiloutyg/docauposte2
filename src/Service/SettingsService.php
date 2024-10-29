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
    public function getSettingsForm(): FormInterface
    {

        $settingsEntity = $this->getSettings();

        $settingsForm = $this->createForm(SettingsType::class, $settingsEntity);

        return $settingsForm;
    }

    // This function is responsible for getting all the settings from the database
    protected function getSettings(): Settings
    {
        $settingsEntity = $this->settingsRepository->getSettings();

        if (!$settingsEntity) {
            $settingsEntity = new Settings();
        }

        return $settingsEntity;    
    }


    // This function is responsible for updating the settings in the database
    public function updateSettings(Request $request): Response
    {        
        // $this->logger->info('Update settings service', [
        //     'request' => $request->request->all()
        // ]);

        $settingsEntity = $this->getSettings();

        $settingsForm = $this->createForm(SettingsType::class, $settingsEntity);

        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $settingsEntity = $settingsForm->getData();
        }

        // $this->logger->info('Settings updated', [
        //     'settingsEntity' => $settingsEntity
        // ]);

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
}
