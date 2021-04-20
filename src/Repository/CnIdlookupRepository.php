<?php

namespace App\Repository;

use App\Entity\CnIdlookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnIdlookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnIdlookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnIdlookup[]    findAll()
 * @method CnIdlookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnIdlookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnIdlookup::class);
    }

    // /**
    //  * @return CnIdlookup[] Returns an array of CnIdlookup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CnIdlookup
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
