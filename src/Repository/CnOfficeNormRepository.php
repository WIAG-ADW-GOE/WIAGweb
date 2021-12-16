<?php

namespace App\Repository;

use App\Entity\CnOfficeNorm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOfficeNorm|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOfficeNorm|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOfficeNorm[]    findAll()
 * @method CnOfficeNorm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficeNormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOfficeNorm::class);
    }

    // /**
    //  * @return CnOfficeNorm[] Returns an array of CnOfficeNorm objects
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
    public function findOneBySomeField($value): ?CnOfficeNorm
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
