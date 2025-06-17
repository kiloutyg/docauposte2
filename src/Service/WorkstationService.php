<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Response;

use App\Repository\WorkstationRepository;

class WorkstationService extends AbstractController
{

    private $em;

    private $workstationRepository;

    public function __construct(
        EntityManagerInterface $em,
        WorkstationRepository $workstationRepository,
    ) {
        $this->em = $em;
        $this->workstationRepository = $workstationRepository;
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
