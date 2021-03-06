<?php

namespace App\Repository;

use App\Entity\CnOnline;
use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOffice;
use App\Entity\CnOfficeGS;
use App\Entity\CnCanonReference;
use App\Entity\CnCanonReferenceGS;
use App\Entity\Domstift;
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

        // join with tables that are needed for sorting anyway
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

    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        // conditions are independent from each other
        // e.g. search for a 'Kanoniker' who had also an office in 'Mainz' says not that the
        // person was 'Kononiker' in 'Mainz';

        # identifier
        if($formmodel->someid) {
            # dump($formmodel->someid);

            $qb->leftJoin('co.idlookup', 'ilt')
               ->andWhere('ilt.authorityId LIKE :someid')
               ->setParameter(':someid', '%'.$formmodel->someid.'%');
        }

        # year
        if($formmodel->year) {
            $qb->join('co.era', 'era')
               ->andWhere('era.eraStart - :mgnyear < :qyear AND :qyear < era.eraEnd + :mgnyear')
               ->setParameter(':mgnyear', self::MARGINYEAR)
               ->setParameter(':qyear', $formmodel->year);
        }

        # domstift
        if($formmodel->monastery) {
            $qb->join('co.officelookup', 'olt_monastery')
               ->join('olt_monastery.monastery', 'monastery')
               ->join('monastery.domstift', 'query_domstift')
               ->andWhere('monastery.monastery_name LIKE :monastery')
               ->setParameter('monastery', '%'.$formmodel->monastery.'%');
        }

        # office title
        if($formmodel->office) {
            $qb->join('co.officelookup', 'olt_office')
               ->andWhere('olt_office.officeName LIKE :office')
               ->setParameter('office', '%'.$formmodel->office.'%');
        }

        # office place
        if($formmodel->place) {
            $qb->join('co.officelookup', 'olt_place')
               ->andWhere('olt_place.locationName LIKE :place OR olt_place.archdeaconTerritory LIKE :place')
               ->setParameter('place', '%'.$formmodel->place.'%');
        }

        # names
        if($formmodel->name) {
            $qb->join('co.namelookup', 'nlt')
               ->andWhere("nlt.givenname LIKE :qname OR nlt.familyname LIKE :qname".
                          " OR nlt.gn_fn LIKE :qname OR nlt.gn_prefix_fn LIKE :qname")
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
               ->andWhere('ocfctl.locationName IN (:locations)')
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
               ->andWhere("ocfctoc.officeName IN (:offices)")
               ->setParameter('offices', $facetOffices);
        }

        return $qb;
    }

    public function addSortParameter($qb, $bishopquery) {

        $sort = 'domstift';

        if ($bishopquery->someid) $sort = 'domstift';
        if ($bishopquery->name) $sort = 'name';
        if ($bishopquery->year) $sort = 'year';
        if ($bishopquery->place) $sort = 'domstift';
        if ($bishopquery->office) $sort = 'domstift';
        $monastery = $bishopquery->monastery;
        if ($monastery) $sort = 'specific_domstift';

        if ($bishopquery->isEmpty() and $bishopquery->facetMonasteries) {
            $facetMonasteries = $bishopquery->facetMonasteries;
            if (count($facetMonasteries) == 1) {
                $sort = 'specific_domstift_id';
                $monastery = $facetMonasteries[0]->getId();
            }
        }

        // this is not possible, because the sorting would be more restrictive as the
        // query condition
        // $monastery_sort_candidate = $this->getIdDomstift($bishopquery->monastery);
        // if (!is_null($monastery_sort_candidate)) {
        //     $sort = 'specific_domstift';
        //     $monastery_sort = $monastery_sort_candidate;
        // }

        /**
         * a reliable order is required
         */
        switch ($sort) {
        case 'specific_domstift_id':
            // strange enough it is more efficient to add officelookup a second time for sorting
            $qb->join('co.officelookup', 'olt_sort')
               ->andWhere('olt_sort.idMonastery = :monastery')
               ->setParameter('monastery', $monastery)
               ->addOrderBy('olt_sort.numdateStart', 'ASC')
               ->addOrderBy('olt_sort.numdateEnd', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'specific_domstift':
            $qb->addOrderBy('olt_monastery.numdateStart', 'ASC')
               ->addOrderBy('olt_monastery.numdateEnd', 'ASC')
               ->leftJoin('co.era', 'era_sort')
               ->addOrderBy('era_sort.eraStart')
               ->addOrderBy('era_sort.eraEnd')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'year':
            $qb->addOrderBy('era.eraStart', 'ASC')
                ->addOrderBy('era.eraEnd', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'name':
            // $qb->orderBy('person.familyname, person.givenname, oc.diocese');
            $qb->join('co.era', 'era', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('era.eraStart', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'domstift':
            $qb->join('co.era', 'era_ds')
               ->addOrderBy('era_ds.domstift', 'ASC')
               ->addOrderBy('era_ds.domstift_start', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        }

        return $qb;

    }

    public function getIdDomstift($monastery_name) {
        if (is_null($monastery_name)) {
            return null;
        }
        $em = $this->getEntityManager();
        $mon = $em->getRepository(Domstift::class)->findOneByName($monastery_name);
        if($mon) {
            return $mon->getGsId();
        } else {
            return null;
        }
    }

    /**
     * return list of monasteries, where persons have an office;
     * used for the facet of monasteries
     */
    public function findOfficePlaces(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('co')
                   ->select('DISTINCT domstift.gsId as id, domstift.name as name, COUNT(DISTINCT(co.id)) as n')
                   ->join('co.officelookup', 'oltmonastery')
                   ->join('oltmonastery.monastery', 'oltdomstift')
                   ->join('oltdomstift.domstift', 'domstift');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('domstift.name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * return list of places, where persons have an office;
     * used for the facet of locations
     */
    public function findOfficeLocations(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('co')
                   ->join('co.officelookup', 'lfacet')
                   ->select('DISTINCT lfacet.locationName, lfacet.locationName, COUNT(DISTINCT(co.id)) as n')
                   ->andWhere('lfacet.locationName IS NOT NULL');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('lfacet.locationName');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function findOfficeNames(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('co')
                   ->select('DISTINCT nfacet.officeName, COUNT(DISTINCT(co.id)) as n')
                   ->join('co.officelookup', 'nfacet')
                   ->andWhere('nfacet.officeName is not NULL');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('nfacet.officeName');

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
            $canon = $em->getRepository(Canon::class)->findOneWithOffices($online->getIdDh());
            $online->setCanonDh($canon);
        } elseif (!is_null($online->getIdGs())) {
            $canon = $em->getRepository(CanonGS::class)->findOneWithOffices($online->getIdGs());
            $online->setCanonGs($canon);
        }
    }

    /*
      Fill the object `online` with data for the detail view.
    */
    public function fillData(CnOnline $online) {
        // this looks not very elegant, but it is simple and each step is easy to control
        $em = $this->getEntityManager();
        if (!is_null($online->getIdDh())) {
            $canon = $em->getRepository(Canon::class)->findOneWithOffices($online->getIdDh());
            $online->setCanonDh($canon);
            # add GS data
            if (!is_null($online->getIdGs())) {
                $this->fillGSOfficesAndReferences($online);
            }
            # add WIAG bishop data
            if (!is_null($online->getIdEp())) {
                # we need the full bishop object here, because of external references
                $personrepo = $em->getRepository(Person::class);
                $episc = $personrepo->findOneWithOffices($online->getIdEp());
                $online->setBishop($episc);
            }
        }
        # GS only
        elseif (!is_null($online->getIdGs())) {
            $canon = $em->getRepository(CanonGS::class)->findOneWithOffices($online->getIdGs());
            $online->setCanonGs($canon);
            # add WIAG bishop data
            if (!is_null($online->getIdEp())) {
                # we need the full bishop object here, because of external references
                $personrepo = $em->getRepository(Person::class);
                $episc = $personrepo->findOneWithOffices($online->getIdEp());
                $online->setBishop($episc);
            }
        }
    }

    public function fillGSOfficesAndReferences(CnOnline $online) {
        $em = $this->getEntityManager();
        $officesgs = $em->getRepository(CnOfficeGS::class)->findByIdCanonAndSort($online->getIdGs());
        $online->setOfficesGs($officesgs);

        $refsrepogs = $em->getRepository(CnCanonReferenceGS::class);
        $refsgs = $refsrepogs->findByIdCanon($online->getIdGs());
        $online->setReferencesGS($refsgs);
        return $online;
    }

    public function findEpisc($episc_id) {
        $em = $this->getEntityManager();
        $personrepo = $em->getRepository(Person::class);
        $episc = $personrepo->findOneWithOffices($episc_id);
        if (!is_null($episc) && $episc->hasMonastery()) {
            $personrepo->addMonasteryLocation($episc);
        }
        return($episc);
    }

    // do not follow the naming convention here (idDh instead of id_dh)
    public function findOneByIdDh($value): ?CnOnline {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.id_dh = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    // do not follow the naming convention here (idGs instead of id_gs)
    public function findOneByIdGs($value): ?CnOnline {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.id_gs = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    // do not follow the naming convention here (idEp instead of id_gs)
    public function findOneByIdEp($value): ?CnOnline {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.id_ep = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getOneOrNullResult();
    }


}
