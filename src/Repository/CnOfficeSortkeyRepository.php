<?php

namespace App\Repository;

use App\Entity\CnOfficeSortkey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOfficeSortkey|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOfficeSortkey|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOfficeSortkey[]    findAll()
 * @method CnOfficeSortkey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficeSortkeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOfficeSortkey::class);
    }

    // /**
    //  * @return CnOfficeSortkey[] Returns an array of CnOfficeSortkey objects
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
    public function findOneBySomeField($value): ?CnOfficeSortkey
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
