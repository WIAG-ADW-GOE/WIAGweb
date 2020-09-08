<?php

namespace App\Repository;

use App\Entity\Givennamevariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Givennamevariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Givennamevariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Givennamevariant[]    findAll()
 * @method Givennamevariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GivennamevariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Givennamevariant::class);
    }

    // /**
    //  * @return Givennamevariant[] Returns an array of Givennamevariant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Givennamevariant
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
