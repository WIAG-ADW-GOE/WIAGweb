<?php

namespace App\Repository;

use App\Entity\Canon;
use App\Entity\CnOffice;
use App\Entity\Monastery;
use App\Form\Model\CanonFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method Canon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Canon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Canon[]    findAll()
 * @method Canon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CanonRepository extends ServiceEntityRepository {
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Canon::class);
    }

    // /**
    //  * @return Canon[] Returns an array of Canon objects
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
    public function findOneBySomeField($value): ?Canon
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

     public function findOfficeNames(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->select('DISTINCT oc.officeName, COUNT(DISTINCT(canon.id)) as n')
                   ->join('canon.offices', 'oc');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('oc.officeName');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * return list of monasteries, where persons have an office;
     * used for the facet of places
     */
    public function findOfficePlaces(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->select('DISTINCT mfacet.wiagid, mfacet.monastery_name, COUNT(DISTINCT(canon.id)) as n')
                   ->join('canon.offices', 'oc')
                   ->join('oc.monastery', 'mfacet')
                   ->andWhere("mfacet.wiagid IN (:domstifte)")
                   ->setParameter('domstifte', Monastery::IDS_DOMSTIFTE);

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('mfacet.monastery_name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        $prefix = "Domstift";
        foreach ($result as $key => $value) {
            $result[$key]['monastery_name'] = Monastery::trimDomstift($result[$key]['monastery_name']);
        }
        return $result;
    }

    /**
     * return list of places, where persons have an office;
     * used for the facet of locations
     */
    public function findOfficeLocations(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->join('canon.offices', 'lfacet')
                   ->select('DISTINCT lfacet.location, lfacet.location, COUNT(DISTINCT(canon.id)) as n')
                   ->andWhere('canon.isready = 1')
                   ->andWhere('lfacet.location IS NOT NULL');


        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('lfacet.location');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * add conditions set by facets
     */
    public function addFacets($querydata, $qb) {
        if($querydata->facetLocations) {
            $locations = array_column($querydata->facetLocations, 'id');
            $qb->join('canon.offices', 'ocfctl')
               ->andWhere('ocfctl.location IN (:locations)')
               ->setParameter('locations', $locations);
        }
        if($querydata->facetMonasteries) {
            $ids_monastery = array_column($querydata->facetMonasteries, 'id');
            // $facetMonasteries = array_map(function($a) {return 'Domstift '.$a;}, $facetMonasteries);
            $qb->join('canon.offices', 'ocfctp')
               ->join('ocfctp.monastery', 'mfctp')
               ->andWhere('mfctp.wiagid IN (:places)')
               ->setParameter('places', $ids_monastery);
        }
        if($querydata->facetOffices) {
            $facetOffices = array_column($querydata->facetOffices, 'name');
            $qb->join('canon.offices', 'ocfctoc')
               ->andWhere("ocfctoc.officeName IN (:offices)")
               ->setParameter('offices', $facetOffices);
        }

        return $qb;
    }


}
