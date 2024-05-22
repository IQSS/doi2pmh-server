<?php

namespace App\Repository;

use App\Entity\Doi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Doi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doi::class);
    }

    /**
     * @param bool $withDeleted
     * @return Doi[]
     */
    public function findAll(bool $withDeleted = true): array
    {
        if ($withDeleted) {
            return parent::findAll();
        }
        return parent::findBy(['deleted' => false]);
    }
}
