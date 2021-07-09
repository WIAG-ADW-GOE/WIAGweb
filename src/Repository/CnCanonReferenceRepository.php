<?php

namespace App\Repository;

use App\Entity\CnCanonReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnCanonReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnCanonReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnCanonReference[]    findAll()
 * @method CnCanonReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnCanonReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnCanonReference::class);
    }

    // /**
    //  * @return CnCanonReference[] Returns an array of CnCanonReference objects
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
    public function findOneBySomeField($value): ?CnCanonReference
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function deleteByIdCanonOrig($id) {
        $qb = $this->createQueryBuilder('cr')
                   ->delete()
                   ->andWhere('cr.idCanonOrig = :id')
                   ->setParameter('id', $id);
        $qb->getQuery()->getResult();
    }
}
