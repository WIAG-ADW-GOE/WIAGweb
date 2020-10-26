<?php

namespace App\Repository;

use App\Entity\IdExternalUrlsDiocese;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IdExternalUrlsDiocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method IdExternalUrlsDiocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method IdExternalUrlsDiocese[]    findAll()
 * @method IdExternalUrlsDiocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IdExternalUrlsDioceseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IdExternalUrlsDiocese::class);
    }

    // /**
    //  * @return IdExternalUrlsDiocese[] Returns an array of IdExternalUrlsDiocese objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IdExternalUrlsDiocese
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
