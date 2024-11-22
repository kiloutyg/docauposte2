<?php

namespace App\Repository;

use App\Entity\Upload;
use App\Entity\User;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Upload::class);
    }

    public function save(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Upload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Get uploads with optional associations and filters.
     *
     * @param array $associations
     * @param bool  $validatedOnly
     * @return Upload[]
     */
    private function getUploads(array $associations = [], bool $validatedOnly = false)
    {
        $qb = $this->createQueryBuilder('u');

        // Resolve and include dependencies
        $resolvedAssociations = $this->resolveAssociations($associations);

        // Always join 'button' association
        $qb->leftJoin('u.button', 'b')
            ->addSelect('b');

        // Always join 'validation' association
        $qb->leftJoin('u.validation', 'v')
            ->addSelect('v');

        // Conditionally join associations based on resolved associations
        if (in_array('category', $resolvedAssociations)) {
            $qb->leftJoin('b.category', 'c')
                ->addSelect('c');
        }

        if (in_array('productLine', $resolvedAssociations)) {
            $qb->leftJoin('c.productLine', 'p')
                ->addSelect('p');
        }

        if (in_array('zone', $resolvedAssociations)) {
            $qb->leftJoin('p.zone', 'z')
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
     * Resolve associations to include dependencies.
     *
     * @param array $associations
     * @return array
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

    public function findAllValidatedUploadsWithAssociations()
    {
        return $this->getUploads(
            ['zone'],
            true
        );
    }

    public function findAllWithAssociations()
    {
        return $this->getUploads(
            ['zone']
        );
    }

    public function findAllWithAssociationsProductLine()
    {
        return $this->getUploads(
            ['productLine']
        );
    }

    public function findAllWithAssociationsCategory()
    {
        return $this->getUploads(
            ['category']
        );
    }

    public function findAllWithAssociationsButton()
    {
        return $this->getUploads();
    }


    //    /**
    //     * @return Upload[] Returns an array of Upload objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Upload
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
