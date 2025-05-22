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
