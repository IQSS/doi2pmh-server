<?php

namespace App\Repository;

use App\Entity\Doi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Doi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Doi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Doi[]    findAll()
 * @method Doi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DoiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doi::class);
    }

    // /**
    //  * @return Doi[] Returns an array of Doi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Doi
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
