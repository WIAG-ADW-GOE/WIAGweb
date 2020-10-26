<?php

namespace App\Repository;

use App\Entity\AltLabelDiocese;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AltLabelDiocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method AltLabelDiocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method AltLabelDiocese[]    findAll()
 * @method AltLabelDiocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AltLabelDioceseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AltLabelDiocese::class);
    }

    // /**
    //  * @return AltLabelDiocese[] Returns an array of AltLabelDiocese objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AltLabelDiocese
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
