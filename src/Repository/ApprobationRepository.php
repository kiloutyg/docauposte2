<?php

namespace App\Repository;

use App\Entity\Approbation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Approbation>
 *
 * @method Approbation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Approbation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Approbation[]    findAll()
 * @method Approbation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprobationRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Approbation::class);
    }
}
