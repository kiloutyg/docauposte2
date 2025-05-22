<?php

namespace App\Repository;

use App\Entity\Validation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Validation>
 *
 * @method Validation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Validation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Validation[]    findAll()
 * @method Validation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValidationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Validation::class);
    }


    /**
     * Retrieves all validation records that have not been validated.
     *
     * This method finds all validation entities where the status is either
     * explicitly set to false or is null (not set).
     *
     * @return Validation[] An array of non-validated Validation entities
     */
    public function findNonValidatedValidations()
    {
        return $this->createQueryBuilder('v')
            ->where('v.status != true OR v.status IS NULL')
            ->getQuery()
            ->getResult();
    }
}
