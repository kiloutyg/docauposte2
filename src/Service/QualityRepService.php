<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

use App\Repository\QualityRepRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

class QualityRepService extends AbstractController
{
    private $em;
    private $logger;

    private $qualityRepRepository;
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,

        QualityRepRepository $qualityRepRepository,
    ) {
        $this->em = $em;
        $this->logger = $logger;

        $this->qualityRepRepository = $qualityRepRepository;
    }

    public function qualityRepCreationFormProcessing(Form $qualityRepForm): string
    {
        try {
            $qualityRepData = $qualityRepForm->getData();
            $this->em->persist($qualityRepData);
            $this->em->flush();
        } finally {
            return $qualityRepData->getUser()->getUsername();
        }
    }
}
