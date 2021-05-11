<?php

namespace App\Repository;

use App\Entity\CnReferenceGS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnReference[]    findAll()
 * @method CnReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnReferenceGSRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnReference::class);
    }

    // /**
    //  * @return CnReference[] Returns an array of CnReference objects
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
    public function findOneBySomeField($value): ?CnReference
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
