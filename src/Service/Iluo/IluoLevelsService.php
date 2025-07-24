<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class IluoLevelsService extends AbstractController
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
     * Processes the ILUO levels creation form by extracting data, formatting it, and persisting to database.
     *
     * This method handles the complete workflow of processing a submitted ILUO levels form:
     * - Extracts the form data
     * - Converts the name to uppercase for consistency
     * - Persists the entity to the database
     * - Returns the processed name regardless of success or failure
     *
     * @param Form $iluoLevelsForm The submitted form containing ILUO levels data to be processed
     *
     * @return string The uppercase name of the ILUO level that was processed
     */
    public function iluoLevelsCreationFormProcessing(Form $iluoLevelsForm): string
    {
        $this->logger->debug(message: 'Processing iluoLevels creation form', context: [$iluoLevelsForm]);
        try {
            $iluoLevelsData = $iluoLevelsForm->getData();
            $iluoLevelsData->setLevel(strtoupper($iluoLevelsData->getLevel()));

            $this->em->persist($iluoLevelsData);
            $this->em->flush();
        } finally {
            return $iluoLevelsData->getLevel();
        }
    }
}
