<?php

namespace App\Repository;

use App\Entity\Era;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Era|null find($id, $lockMode = null, $lockVersion = null)
 * @method Era|null findOneBy(array $criteria, array $orderBy = null)
 * @method Era[]    findAll()
 * @method Era[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Era::class);
    }

    // /**
    //  * @return Era[] Returns an array of Era objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Era
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
