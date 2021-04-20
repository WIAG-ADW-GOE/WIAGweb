<?php

namespace App\Repository;

use App\Entity\CnOfficelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOfficelookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOfficelookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOfficelookup[]    findAll()
 * @method CnOfficelookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficelookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOfficelookup::class);
    }

    // /**
    //  * @return CnOfficelookup[] Returns an array of CnOfficelookup objects
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
    public function findOneBySomeField($value): ?CnOfficelookup
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /* AJAX callback */
    public function suggestPlace($place, $limit = 100): array {
        $qb = $this->createQueryBuilder('olt')
                   ->select('DISTINCT olt.location_name AS suggestion')
                   ->andWhere('olt.location_name LIKE :place')
                   ->setParameter('place', '%'.$place.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();

    }

    /* AJAX callback */
    public function suggestOffice($office, $limit = 100): array {
        $qb = $this->createQueryBuilder('olt')
                   ->select('DISTINCT olt.office_name AS suggestion')
                   ->andWhere('olt.office_name LIKE :title')
                   ->setParameter('title', '%'.$office.'%')
                   ->setMaxResults($limit);

        $query = $qb->getQuery();

        return $query->getResult();

    }


    
}
