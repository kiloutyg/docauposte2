<?php

namespace App\Repository;

use App\Entity\OldUpload;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OldUpload>
 *
 * @method OldUpload|null find($id, $lockMode = null, $lockVersion = null)
 * @method OldUpload|null findOneBy(array $criteria, array $orderBy = null)
 * @method OldUpload[]    findAll()
 * @method OldUpload[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OldUploadRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OldUpload::class);
    }

    public function save(OldUpload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OldUpload $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
