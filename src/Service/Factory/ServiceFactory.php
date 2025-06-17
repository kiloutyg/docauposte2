<?php

namespace App\Service\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * ServiceFactory
 *
 * This class is responsible for dynamically retrieving Service instances
 * based on the entity type. It provides a flexible way to access services
 * without directly injecting them into other services.
 */
class ServiceFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ContainerInterface $container The service container
     * @param LoggerInterface $logger Optional logger for recording service access
     */
    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger
    ) {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * Get the Service for the specified entity type
     *
     * This method attempts to retrieve the Service from the container.
     * If not found, it throws an exception as services should be properly registered.
     *
     * @param string $className The entity type (e.g., 'User', 'Product')
     * @return object The Service instance
     * @throws \InvalidArgumentException If the service is not found
     */
    public function getService(string $className)
    {
        // Construct the fully qualified Service class name
        $serviceClass = 'App\\Service\\' . ucfirst($className) . 'Service';

        // Check if the Service is available as a service in the container
        if ($this->container->has($serviceClass)) {
            if ($this->logger) {
                $this->logger->debug("Retrieved service {$serviceClass} from container");
            }
            return $this->container->get($serviceClass);
        }


        // Unlike repositories which can fall back to entity manager,
        // services must be explicitly defined in the container
        throw new \InvalidArgumentException(
            "Service for entity type '{$className}' not found. " .
                "Make sure the service is properly registered in the container."
        );
    }
}
