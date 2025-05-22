<?php

namespace App\Repository;

use App\Entity\Button;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Button>
 *
 * @method Button|null find($id, $lockMode = null, $lockVersion = null)
 * @method Button|null findOneBy(array $criteria, array $orderBy = null)
 * @method Button[]    findAll()
 * @method Button[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ButtonRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Button::class);
    }

    public function save(Button $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Button $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
