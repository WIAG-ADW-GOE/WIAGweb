<?php

namespace App\Repository;

use App\Entity\Namelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Namelookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method Namelookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method Namelookup[]    findAll()
 * @method Namelookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NamelookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Namelookup::class);
    }

    // /**
    //  * @return Namelookup[] Returns an array of Namelookup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Namelookup
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
