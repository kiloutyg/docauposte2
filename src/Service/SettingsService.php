<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\FormInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

use App\Entity\Department;
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
    /**
     * Gets the settings form.
     *
     * This function is responsible for getting the settings from the database and creating a form.
     *
     * @return FormInterface The form object containing the settings entity
     */
    public function getSettingsForm(): FormInterface
    {
        $settingsEntity = $this->getSettings();

        return $this->createForm(SettingsType::class, $settingsEntity);
    }



    // This function is responsible for getting all the settings from the database
    /**
     * Retrieves the application settings from the database.
     *
     * This function checks if settings are already loaded in memory. If not, it fetches them
     * from the repository. If no settings exist in the database, it creates default settings
     * with two departments ('I.T.' and 'QUALITY') and persists them to the database.
     *
     * @return Settings The settings entity containing all application configuration
     */
    public function getSettings(): Settings
    {
        if (null === $this->settings) {
            // Assuming getSettings returns an associative array or an object with your settings
            $this->settings = $this->settingsRepository->getSettings();
        }
        if (!$this->settings) {
            $department = new Department();
            $department->setName('I.T.');
            $this->em->persist($department);
            $department = new Department();
            $department->setName('QUALITY');
            $this->em->persist($department);

            $settingsEntity = new Settings();
            $this->em->persist($settingsEntity);
            $this->em->flush();
            $this->settings = $settingsEntity;
        }

        return $this->settings;
    }



    // This function is responsible for updating the settings in the database
    /**
     * Updates the application settings in the database.
     *
     * This function processes the submitted settings form, validates the data,
     * and persists the updated settings to the database. If successful, it returns
     * a Response with a boolean true. If an error occurs during the update process,
     * it logs the error and returns a Response containing the error message.
     *
     * @param Request $request The HTTP request containing the submitted form data
     * @return Response A Response object containing either true on success or an error message on failure
     */
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



    /**
     * Retrieves the auto-display timer duration for incidents in seconds.
     *
     * This function checks if the timer value is already loaded in memory.
     * If not, it fetches the value from the settings repository.
     * The timer controls how long incidents are automatically displayed.
     *
     * @return int|null The incident auto-display timer duration in seconds
     */
    public function getIncidentAutoDisplayTimerInSeconds()
    {
        if ($this->incidentAutoDisplayTimerInSeconds === null) {
            $this->incidentAutoDisplayTimerInSeconds = $this->settingsRepository->getIncidentAutoDisplayTimerInSeconds();
        }
        return $this->incidentAutoDisplayTimerInSeconds;
    }


    /**
     * Retrieves the current operator code regex pattern from settings.
     *
     * This function gets the regular expression pattern used for validating
     * operator codes from the application settings.
     *
     * @return string The regular expression pattern for operator code validation
     */
    public function getCurrentCodeOpeRegexPattern(): string
    {
        return $this->getSettings()->getOperatorCodeRegex();
    }
}
