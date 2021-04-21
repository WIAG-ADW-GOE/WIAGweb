<?php

namespace App\Repository;

use App\Entity\CnCanonReferenceGS;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnCanonReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnCanonReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnCanonReference[]    findAll()
 * @method CnCanonReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnCanonReferenceGSRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnCanonReferenceGS::class);
    }

    // /**
    //  * @return CnCanonReferenceGS[] Returns an array of CnCanonReferenceGS objects
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
    public function findOneBySomeField($value): ?CnCanonReferenceGS
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
