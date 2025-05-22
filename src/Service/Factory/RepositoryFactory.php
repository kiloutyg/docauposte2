<?php

namespace App\Service\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * RepositoryFactory
 *
 * This class is responsible for dynamically retrieving repository instances
 * based on the entity type. It provides a flexible way to access repositories
 * without directly injecting them into services.
 */
class RepositoryFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor
     *
     * @param ContainerInterface $container The service container
     * @param EntityManagerInterface $em The entity manager
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em
    ) {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * Get the repository for the specified entity type
     *
     * This method attempts to retrieve the repository from the container first.
     * If not found, it falls back to using the EntityManager to get the repository.
     *
     * @param string $entityType The entity type (e.g., 'User', 'Product')
     * @return object The repository instance
     */
    public function getRepository(string $entityType)
    {
        // Construct the fully qualified repository class name
        $repositoryClass = 'App\\Repository\\' . ucfirst($entityType) . 'Repository';

        // Check if the repository is available as a service in the container
        if ($this->container->has($repositoryClass)) {
            return $this->container->get($repositoryClass);
        }

        // If not found in the container, use the EntityManager to get the repository
        return $this->em->getRepository('App\\Entity\\' . ucfirst($entityType));
    }
}
