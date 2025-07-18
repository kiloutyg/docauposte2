<?php

namespace App\Repository;

use App\Entity\Upload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Upload>
 *
 * @method Upload|null find($id, $lockMode = null, $lockVersion = null)
 * @method Upload|null findOneBy(array $criteria, array $orderBy = null)
 * @method Upload[]    findAll()
 * @method Upload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadRepository extends BaseRepository
{
    /**
     * Constructor for the UploadRepository.
     *
     * Initializes the repository with the entity manager and sets Upload as the entity class.
     *
     * @param ManagerRegistry $registry The Doctrine registry service that provides access to entity managers
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Upload::class);
    }


    /**
     * Saves an Upload entity to the database.
     *
     * This method persists the given Upload entity to the database. If the flush parameter
     * is set to true, changes will be immediately written to the database. Otherwise,
     * the entity will only be scheduled for insertion/update on the next flush operation.
     *
     * @param Upload $entity The Upload entity to be saved
     * @param bool $flush Whether to flush changes immediately to the database (default: false)
     * @return void
     */
    public function save(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * Removes an Upload entity from the database.
     *
     * This method marks the given Upload entity for removal from the database. If the flush parameter
     * is set to true, changes will be immediately written to the database. Otherwise,
     * the entity will only be scheduled for deletion on the next flush operation.
     *
     * @param Upload $entity The Upload entity to be removed
     * @param bool $flush Whether to flush changes immediately to the database (default: false)
     * @return void
     */
    public function remove(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }



    /**
     * Retrieves uploads with specified associations and optional validation filtering.
     *
     * This method builds a query to fetch Upload entities with their related associations
     * (button, validation, category, productLine, zone) based on the provided parameters.
     * It automatically resolves dependencies between associations to ensure proper joins.
     *
     * @param array $associations An array of association names to include in the query results
     *                           (e.g., ['zone', 'category', 'productLine'])
     * @param bool $validatedOnly When true, returns only uploads that have been validated with status=1
     *
     * @return Upload[] An array of Upload entities with the requested associations loaded
     */
    private function getUploads(array $associations = [], bool $validatedOnly = false)
    {
        $qb = $this->createQueryBuilder('u');

        // Resolve and include dependencies
        $resolvedAssociations = $this->resolveAssociations($associations);

        // Always join 'button' association
        $qb->leftJoin('u.button', 'b')
            ->orderBy('b.SortOrder', 'ASC')
            ->addSelect('b');

        // Always join 'validation' association
        $qb->leftJoin('u.validation', 'v')
            ->addSelect('v');

        // Conditionally join associations based on resolved associations
        if (in_array('category', $resolvedAssociations)) {
            $qb->leftJoin('b.category', 'c')
                ->orderBy('c.SortOrder', 'ASC')
                ->addSelect('c');
        }

        if (in_array('productLine', $resolvedAssociations)) {
            $qb->leftJoin('c.productLine', 'p')
                ->orderBy('p.SortOrder', 'ASC')
                ->addSelect('p');
        }

        if (in_array('zone', $resolvedAssociations)) {
            $qb->leftJoin('p.zone', 'z')
                ->orderBy('z.SortOrder', 'ASC')
                ->addSelect('z');
        }

        // Apply validation filter if needed
        if ($validatedOnly) {
            $qb->where('v.id IS NOT NULL')
                ->andWhere('v.status = 1');
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Resolves associations to include their required dependencies.
     *
     * This method ensures that when requesting specific associations (like 'zone'),
     * all required parent associations in the hierarchy (like 'productLine', 'category')
     * are also included in the result.
     *
     * @param array $associations An array of association names to be resolved (e.g., ['zone', 'category'])
     * @return array An array of unique association names including all dependencies
     */
    private function resolveAssociations(array $associations): array
    {
        // Define the dependencies for each association
        $dependencies = [
            'zone' => ['productLine', 'category', 'button'],
            'productLine' => ['category', 'button'],
            'category' => ['button'],
            'button' => [],
        ];

        $resolved = [];

        foreach ($associations as $association) {
            // Add the association itself
            $resolved[] = $association;

            // Recursively add dependencies
            if (isset($dependencies[$association])) {
                $resolved = array_merge($resolved, $dependencies[$association]);
            }
        }

        // Always include 'button' as it's a direct association
        if (!in_array('button', $resolved)) {
            $resolved[] = 'button';
        }

        // Remove duplicates
        $resolved = array_unique($resolved);

        return $resolved;
    }


    /**
     * Retrieves all validated uploads with their complete association hierarchy.
     *
     * This method returns all Upload entities that have been validated (status=1),
     * including their full association chain: button, validation, category, productLine, and zone.
     *
     * @return Upload[] An array of validated Upload entities with all associations loaded
     */
    public function findAllValidatedUploadsWithAssociations()
    {
        return $this->getUploads(
            ['zone'],
            true
        );
    }


    /**
     * Retrieves all uploads with their complete association hierarchy.
     *
     * This method returns all Upload entities including their full association chain:
     * button, validation, category, productLine, and zone.
     *
     * @return Upload[] An array of Upload entities with all associations loaded
     */
    public function findAllWithAssociations()
    {
        return $this->getUploads(
            ['zone']
        );
    }


    /**
     * Retrieves all uploads with associations up to the product line level.
     *
     * This method returns all Upload entities with their associations loaded
     * including button, validation, category, and productLine, but not zone.
     *
     * @return Upload[] An array of Upload entities with product line associations loaded
     */
    public function findAllWithAssociationsProductLine()
    {
        return $this->getUploads(
            ['productLine']
        );
    }


    /**
     * Retrieves all uploads with associations up to the category level.
     *
     * This method returns all Upload entities with their associations loaded
     * including button, validation, and category, but not productLine or zone.
     *
     * @return Upload[] An array of Upload entities with category associations loaded
     */
    public function findAllWithAssociationsCategory()
    {
        return $this->getUploads(
            ['category']
        );
    }


    /**
     * Retrieves all uploads with associations up to the button level.
     *
     * This method returns all Upload entities with their basic associations loaded
     * including button and validation, but not category, productLine, or zone.
     *
     * @return Upload[] An array of Upload entities with button associations loaded
     */
    public function findAllWithAssociationsButton()
    {
        return $this->getUploads();
    }
}
