<?php

namespace App\Repository;

use App\Entity\CanonGS;
use App\Entity\CnOfficeGS;
use App\Entity\Monastery;
use App\Form\Model\CanonFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method CanonGS|null find($id, $lockMode = null, $lockVersion = null)
 * @method CanonGS|null findOneBy(array $criteria, array $orderBy = null)
 * @method CanonGS[]    findAll()
 * @method CanonGS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CanonGSRepository extends ServiceEntityRepository {
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CanonGS::class);
    }

    // /**
    //  * @return CanonGS[] Returns an array of CanonGS objects
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
    public function findOneBySomeField($value): ?CanonGS
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function findOneWithOffices($id) {
        // fetch all data related to this canon
        // sorting of offices is specified by an annotation to CanonGS.offices
        $query = $this->createQueryBuilder('canon')
                      ->leftJoin('canon.offices', 'oc')
                      ->addSelect('oc')
                      ->andWhere('canon.id = :id')
                      ->setParameter('id', $id)
                      ->getQuery();

        $canon = $query->getOneOrNullResult();
        return $canon;
    }

    public function findIdGsByGsnId($gsn_id) {
        $query = $this->createQueryBuilder('canon')
                      ->select('canon.Id')
                      ->andWhere('canon.gsnId = :gsn_id')
                      ->setParameter('gsn_id', $gsn_id)
                      ->getQuery();
        $id_gs = $query->getOneOrNullResult();
        return $id_gs;
    }

    /** AJAX callback */
    public function suggestGsn($input, $limit = 200): array {
        $qb = $this->createQueryBuilder('c')
                   ->select('DISTINCT c.gsnId AS suggestion')
                   ->join('\App\Entity\CnOnline', 'co', 'WITH', 'co.id_gs = c.id')
                   ->andWhere('c.gsnId LIKE :input')
                   ->andWhere('co.id_dh IS NULL')
                   ->setParameter('input', '%'.$input.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();

    }

}
