<?php

namespace App\Repository;

use App\Entity\ExternalUrlType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExternalUrlType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExternalUrlType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExternalUrlType[]    findAll()
 * @method ExternalUrlType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExternalUrlTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalUrlType::class);
    }

    // /**
    //  * @return ExternalUrlType[] Returns an array of ExternalUrlType objects
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
    public function findOneBySomeField($value): ?ExternalUrlType
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
