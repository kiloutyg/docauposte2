<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class StepsTitleService extends AbstractController
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
     * Processes the stepsTitle creation form and persists the data to the database.
     *
     * @param Form $stepsTitleForm The form containing the stepsTitle data.
     *
     * @return string The title of the created stepsTitle.
     *
     * @throws Exception If an error occurs during the database operations.
     */
    public function stepsTitleCreationFormProcessing(Form $stepsTitleForm): string
    {
        $this->logger->debug(message: 'StepsTitleService::stepsTitleCreationFormProcessing - Processing stepsTitle creation form', context: [$stepsTitleForm]);
        try {
            $stepsTitleData = $stepsTitleForm->getData();

            $this->em->persist($stepsTitleData);
            $this->em->flush();
        } finally {
            return $stepsTitleData->getTitle();
        }
    }
}
