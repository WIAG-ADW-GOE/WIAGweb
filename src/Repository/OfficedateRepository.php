<?php

namespace App\Repository;

use App\Entity\Officedate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Officedate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Officedate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Officedate[]    findAll()
 * @method Officedate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfficedateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Officedate::class);
    }

    // /**
    //  * @return Officedate[] Returns an array of Officedate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Officedate
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
