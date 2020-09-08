<?php

namespace App\Repository;

use App\Entity\Familynamevariant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Familynamevariant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Familynamevariant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Familynamevariant[]    findAll()
 * @method Familynamevariant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamilynamevariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Familynamevariant::class);
    }

    // /**
    //  * @return Familynamevariant[] Returns an array of Familynamevariant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Familynamevariant
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
