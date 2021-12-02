<?php

namespace App\Repository;

use App\Entity\CnOfficeDesignation;
use App\Entity\CnOfficelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOfficeDesignation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOfficeDesignation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOfficeDesignation[]    findAll()
 * @method CnOfficeDesignation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficeDesignationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOfficeDesignation::class);
    }

    // /**
    //  * @return CnOfficeDesignation[] Returns an array of CnOfficeDesignation objects
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
    public function findOneBySomeField($value): ?CnOfficeDesignation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByMonastery($monasteryName) {
        $qb = $this->createQueryBuilder('dsgn')
                   ->join('\App\Entity\CnOfficelookup', 'olt', 'WITH', 'olt.officeName = dsgn.nameSingular')
                   ->andWhere('olt.domstift = :monasteryName')
                   ->setParameter(':monasteryName', $monasteryName)
                   ->groupBy('dsgn.id')
                   ->addOrderBy('dsgn.sortkey');

        $query = $qb->getQuery();

        return $query->getResult();
    }

}
