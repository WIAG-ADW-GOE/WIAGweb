<?php

namespace App\Repository;

use App\Entity\CnOnline;
use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOffice;
use App\Entity\CnOfficeGS;
use App\Entity\CnCanonReference;
use App\Entity\CnCanonReferenceGS;
use App\Entity\Person;
use App\Entity\Monastery;
use App\Form\Model\CanonFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method CnOnline|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOnline|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOnline[]    findAll()
 * @method CnOnline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOnlineRepository extends ServiceEntityRepository {
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry) {

        parent::__construct($registry, CnOnline::class);
    }

    // /**
    //  * @return CnOnline[] Returns an array of CnOnline objects
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
    public function findOneBySomeField($value): ?CnOnline
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function countByQueryObject(CanonFormModel $formmodel) {
        // if($formmodel->isEmpty()) return 0;
        $qb = $this->createQueryBuilder('co')
                   ->select('COUNT(DISTINCT co.id)');

        $this->addQueryConditions($qb, $formmodel);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findByQueryObject(CanonFormModel $formmodel, $limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('co');

        $this->addQueryConditions($qb, $formmodel);


        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());
        $this->addSortParameter($qb, $formmodel);

        $query = $qb->getQuery();
        // dd($query->getResult());
        $persons = new Paginator($query, true);

        return $persons;
    }

    public function findAllWithLimit($limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('co')
                   ->leftJoin('co.officesortkey', 'os')
                   ->addOrderBy('os.location_name', 'ASC')
                   ->addOrderBy('os.numdate_start', 'ASC')
                   ->addOrderBy('os.numdate_end', 'ASC')
                   ->addOrderBy('co.id', 'ASC');

        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        $query = $qb->getQuery();
        // dd($query->getResult());
        $persons = new Paginator($query, true);

        return $persons;
    }


    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        # identifier
        if($formmodel->someid) {
            $db_id = Canon::extractDbId($formmodel->someid);
            $id_param = $db_id ? $db_id : $formmodel->someid;
            // dump($db_id, $id_param);

            $qb->join('co.idlookup', 'ilt')
               ->andWhere('ilt.authority_id = :someid OR co.id = :someid')
               ->setParameter(':someid', $id_param);
        }

        # year
        if($formmodel->year) {
            $erajoined = true;
            $qb->join('co.era', 'era')
                ->andWhere('era.eraStart - :mgnyear < :qyear AND :qyear < era.eraEnd + :mgnyear')
                ->setParameter(':mgnyear', self::MARGINYEAR)
                ->setParameter(':qyear', $formmodel->year);
        }

        # monastery
        if($formmodel->monastery) {
            $qb->join('co.officelookup', 'olt_monastery')
               ->join('olt_monastery.monastery', 'monastery')
                ->andWhere('monastery.monastery_name LIKE :monastery')
                ->setParameter('monastery', '%'.$formmodel->monastery.'%');
        }

        # office title
        if($formmodel->office) {
            $qb->join('co.officelookup', 'olt_name')
               ->andWhere('olt_name.office_name LIKE :office')
               ->setParameter('office', '%'.$formmodel->office.'%');
        }

        # office place
        if($formmodel->place) {
            $qb->join('co.officelookup', 'olt_place')
                ->andWhere('olt_place.location_name LIKE :place OR olt_place.archdeacon_territory LIKE :place')
                ->setParameter('place', '%'.$formmodel->place.'%');
        }
        # names
        if($formmodel->name) {
            $qb->join('co.namelookup', 'nlt')
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefixName, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->setParameter('qname', '%'.$formmodel->name.'%');
        }

        $this->addFacets($formmodel, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    /**
     * add conditions set by facets
     */
    public function addFacets($querydata, $qb) {
        if($querydata->facetLocations) {
            $locations = array_column($querydata->facetLocations, 'id');
            $qb->join('co.officelookup', 'ocfctl')
               ->andWhere('ocfctl.location_name IN (:locations)')
               ->setParameter('locations', $locations);
        }
        if($querydata->facetMonasteries) {
            $ids_monastery = array_column($querydata->facetMonasteries, 'id');
            // $facetMonasteries = array_map(function($a) {return 'Domstift '.$a;}, $facetMonasteries);
            $qb->join('co.officelookup', 'ocfctp')
               ->join('ocfctp.monastery', 'mfctp')
               ->andWhere('mfctp.wiagid IN (:places)')
               ->setParameter('places', $ids_monastery);
        }
        if($querydata->facetOffices) {
            $facetOffices = array_column($querydata->facetOffices, 'name');
            $qb->join('co.officelookup', 'ocfctoc')
               ->andWhere("ocfctoc.office_name IN (:offices)")
               ->setParameter('offices', $facetOffices);
        }

        return $qb;
    }



    public function addSortParameter($qb, $bishopquery) {

        $sort = 'location';
        if($bishopquery->someid) $sort = 'year';
        if($bishopquery->year) $sort = 'year';
        if($bishopquery->name) $sort = 'name';
        if($bishopquery->place) $sort = 'location';
        if($bishopquery->office) $sort = 'location';
        if($bishopquery->showAll) $sort = 'location';
        /**
         * a reliable order is required, therefore person.givenname shows up
         * in each sort clause
         */

        switch($sort) {
        case 'year':
            $qb->leftJoin('co.era', 'erasort')
               ->addOrderBy('erasort.eraStart', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'name':
            // $qb->orderBy('person.familyname, person.givenname, oc.diocese');
            $qb->addOrderBy('nlt.familyname', 'ASC')
               ->addOrderBy('nlt.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'location':
            $qb->leftJoin('co.officesortkey', 'os')
               ->addOrderBy('os.location_name', 'ASC')
               ->addOrderBy('os.numdate_start', 'ASC')
               ->addOrderBy('os.numdate_end', 'ASC')
               ->addOrderBy('co.id');
            break;
        }

        return $qb;

    }

    /**
     * return list of monasteries, where persons have an office;
     * used for the facet of monasteries
     */
    public function findOfficePlaces(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('co')
                   ->select('DISTINCT mfacet.wiagid, mfacet.monastery_name, COUNT(DISTINCT(co.id)) as n')
                   ->join('co.officelookup', 'oc')
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
        $qb = $this->createQueryBuilder('co')
                   ->join('co.officelookup', 'lfacet')
                   ->select('DISTINCT lfacet.location_name, lfacet.location_name, COUNT(DISTINCT(co.id)) as n')
                   ->andWhere('lfacet.location_name IS NOT NULL');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('lfacet.location_name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function findOfficeNames(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('co')
                   ->select('DISTINCT nfacet.office_name, COUNT(DISTINCT(co.id)) as n')
                   ->join('co.officelookup', 'nfacet')
                   ->andWhere('nfacet.office_name is not NULL');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('nfacet.office_name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /*
      Fill the object `online` with data for the list view.
     */
    public function fillListData(CnOnline $online) {
        $em = $this->getEntityManager();
        if (!is_null($online->getIdDh())) {
            $canon = $em->getRepository(Canon::class)->findOneById($online->getIdDh());
            $online->setCanonDh($canon);
            $officesdh = $em->getRepository(CnOffice::class)->findByIdCanonAndSort($online->getIdDh());
            $online->setOfficesDh($officesdh);
        } elseif (!is_null($online->getIdGs())) {
            $canon = $em->getRepository(CanonGS::class)->findOneById($online->getIdGs());
            $online->setCanonGs($canon);
            $officesgs = $em->getRepository(CnOfficeGS::class)->findByIdCanonAndSort($online->getIdGs());
            $online->setOfficesGs($officesgs);
        }

    }

    /*
      Fill the object `online` with data for the detail view.
    */
    public function fillData(CnOnline $online) {
        // this looks not very elegant, but it is simple and each step is easy to control
        $em = $this->getEntityManager();
        if (!is_null($online->getIdDh())) {
            $canon = $em->getRepository(Canon::class)->findOneById($online->getIdDh());
            $online->setCanonDh($canon);

            $officesdh = $em->getRepository(CnOffice::class)->findByIdCanonAndSort($online->getIdDh());
            $online->setOfficesDh($officesdh);

            $refsrepodh = $em->getRepository(CnCanonReference::class);
            $refsdh = $refsrepodh->findByIdCanon($online->getIdDh());
            $online->setReferencesDh($refsdh);
            # add GS data
            if (!is_null($online->getIdGs())) {
                $officesgs = $em->getRepository(CnOfficeGS::class)->findByIdCanonAndSort($online->getIdGs());
                $online->setOfficesGs($officesgs);

                $refsrepogs = $em->getRepository(CnCanonReferenceGS::class);
                $refsgs = $refsrepogs->findByIdCanon($online->getIdGs());
                $online->setReferencesGS($refsgs);
            }
            # add WIAG bishop data
            $episc_id = $online->getCanonDh()->getWiagEpiscId();
            if ($episc_id) {
                $personrepo = $em->getRepository(Person::class);
                $episc = $personrepo->findOneWithOffices($episc_id);
                if (!is_null($episc) && $episc->hasMonastery()) {
                    $personrepo->addMonasteryLocation($episc);
                }
                $online->setBishop($episc);
                $online->getCanonDh()->copyExternalIds($episc);
            }
        }
        # GS only
        elseif (!is_null($online->getIdGs())) {
            $canon = $em->getRepository(CanonGS::class)->findOneById($online->getIdGs());
            $online->setCanonGs($canon);

            $officesgs = $em->getRepository(CnOfficeGS::class)->findByIdCanonAndSort($online->getIdGs());
            $online->setOfficesGs($officesgs);

            $refsrepogs = $em->getRepository(CnCanonReferenceGS::class);
            $refsgs = $refsrepogs->findByIdCanon($online->getIdGs());
            $online->setReferencesGS($refsgs);
            # add WIAG bishop data
            $episc_id = $online->getCanonGs()->getWiagEpiscId();
            if ($episc_id) {
                $personrepo = $em->getRepository(Person::class);
                $episc = $personrepo->findOneWithOffices($episc_id);
                if (!is_null($episc) && $episc->hasMonastery()) {
                    $personrepo->addMonasteryLocation($episc);
                }
                $online->setBishop($episc);
                $online->getCanonGs()->copyExternalIds($episc);
            }

        }

    }

}
