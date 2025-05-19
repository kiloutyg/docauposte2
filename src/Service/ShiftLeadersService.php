<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use App\Repository\ShiftLeadersRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

class ShiftLeadersService extends AbstractController
{
    private $em;
    private $logger;

    private $shiftLeadersRepository;
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,

        ShiftLeadersRepository $shiftLeadersRepository,
    ) {
        $this->em = $em;
        $this->logger = $logger;

        $this->shiftLeadersRepository = $shiftLeadersRepository;
    }

    public function shiftLeadersCreationFormProcessing(Form $shiftLeaderForm): string
    {
        try {
            $shiftLeaderData = $shiftLeaderForm->getData();
            $this->em->persist($shiftLeaderData);
            $this->em->flush();
        } finally {
            $shiftLeaderName = '';
            if ($shiftLeaderData->getUser()) {
                $shiftLeaderName = $shiftLeaderData->getUser()->getUsername();
            } elseif ($shiftLeaderData->getOperator()) {
                $shiftLeaderName = $shiftLeaderData->getOperator()->getName();
            }
            return $shiftLeaderName;
        }
    }
}
