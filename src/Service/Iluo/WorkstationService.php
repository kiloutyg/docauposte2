<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class WorkstationService extends AbstractController
{

    private $em;
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Processes the workstation creation form by persisting the data to the database.
     *
     * This method extracts data from the submitted form, converts the workstation name
     * to uppercase, and saves the workstation entity to the database.
     *
     * @param Form $workstationForm The submitted form containing workstation data
     *
     * @return string The name of the created workstation (in uppercase)
     */
    public function workstationCreationFormProcessing(Form $workstationForm): string
    {
        $this->logger->debug(message: 'Processing workstation creation form', context: [$workstationForm]);
        try {
            $workstationData = $workstationForm->getData();
            $workstationData->setName(strtoupper($workstationData->getName()));

            $this->em->persist($workstationData);
            $this->em->flush();
        } finally {
            return $workstationData->getName();
        }
    }
}
