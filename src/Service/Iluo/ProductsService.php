<?php

namespace App\Service\Iluo;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Form;

use Psr\Log\LoggerInterface;

class ProductsService extends AbstractController
{

    private $em;
    private $logger;

    /**
     * Constructor for the ProductsService class.
     *
     * Initializes the service with required dependencies for database operations
     * and logging functionality.
     *
     * @param EntityManagerInterface $em     The entity manager for database operations
     * @param LoggerInterface        $logger The logger service for logging events and errors
     */
    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Processes the product creation form by persisting the product data to the database.
     *
     * This method takes a submitted form, extracts the product data, converts the name to uppercase,
     * persists the entity to the database, and returns the product name.
     *
     * @param Form $productForm The submitted product form containing the product data
     *
     * @return string The uppercase name of the created product
     */
    public function productsCreationFormProcessing(Form $productForm): string
    {
        $this->logger->debug(message: 'Processing products creation form', context: [$productForm]);
        try {
            $productData = $productForm->getData();
            $productData->setName(strtoupper(string: $productData->getName()));
            $this->em->persist(object: $productData);
            $this->em->flush();
        } finally {
            return $productData->getName();
        }
    }
}
