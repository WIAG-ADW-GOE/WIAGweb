<?php

namespace App\Repository;

use App\Entity\Monastery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Monastery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Monastery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Monastery[]    findAll()
 * @method Monastery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MonasteryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Monastery::class);
    }

    // /**
    //  * @return Monastery[] Returns an array of Monastery objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Monastery
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}