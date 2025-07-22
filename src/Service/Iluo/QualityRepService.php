<?php

namespace App\Service\Iluo;

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

    /**
     * Processes the quality report creation form by persisting the data to the database.
     *
     * This function extracts data from the submitted form, persists it to the database,
     * and returns the username of the user associated with the quality report.
     *
     * @param Form $qualityRepForm The submitted form containing quality report data
     * @return string The username of the user associated with the quality report
     */
    public function qualityRepCreationFormProcessing(Form $qualityRepForm): string
    {
        $this->logger->debug(message: 'Processing quality report creation form', context: [$qualityRepForm]);
        try {
            $qualityRepData = $qualityRepForm->getData();
            $this->em->persist($qualityRepData);
            $this->em->flush();
        } finally {
            return $qualityRepData->getUser()->getUsername();
        }
    }
}
