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
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


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
        // if ($formmodel->isEmpty()) return 0;
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


        if ($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        $this->addSortParameter($qb, $formmodel);

        $query = $qb->getQuery();

        // dd($query->getResult());
        $persons = new Paginator($query, true);

        return $persons;
    }

    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        // conditions are independent from each other
        // e.g. search for a 'Kanoniker' who had also an office in 'Mainz' means not that the
        // person was 'Kanoniker' in 'Mainz';

        $someid = $formmodel->someid;
        $year = $formmodel->year;
        $monastery = $formmodel->monastery;
        $name = $formmodel->name;
        $office = $formmodel->office;
        $place = $formmodel->place;

        # domstift and combinations
        # exclude combinations with `place`
        $monastery = $monastery;
        if ($monastery) {
            /**
             * workaround to allow search strings like 'Domstift Lebus (...)' and
             * 'Augustinerchorherrenstift Herrenchiemsee'
             */
            $em = $this->getEntityManager();
            $domstifte = $em->getRepository(Domstift::class)->findAll();
            $names = array();
            foreach ($domstifte as $d) {
                $names[] = $d->getName();
            }
            $rgm = array();
            preg_match("/".implode('|', $names)."/i", $monastery, $rgm);

            $monasteryPar = count($rgm) > 0 ? $rgm[0] : $monastery;

            $qb->join('co.officelookup', 'olt_domstift')
               ->join('co.era', 'era_domstift_srt') # for sorting
               ->andWhere('olt_domstift.domstift LIKE :domstift')
               ->andWhere('era_domstift_srt.domstift LIKE :domstift')
               ->setParameter(':domstift', '%'.$monasteryPar.'%');

            # domstift - office
            if ($office) {
                $qb->andWhere('olt_domstift.officeName LIKE :office')
                   ->setParameter('office', '%'.$office.'%');

                # domstift - office - year
                if ($year) {
                    $qb->andWhere("olt_domstift.numdateStart - :mgnyear < :qyear ".
                                  " AND :qyear < olt_domstift.numdateEnd + :mgnyear")
                       ->setParameter(':mgnyear', self::MARGINYEAR)
                       ->setParameter(':qyear', $year);
                }
            }
            # domstift - year
            elseif ($year) {
                $qb->andWhere("olt_domstift.numdateStart - :mgnyear < :qyear ".
                              " AND :qyear < olt_domstift.numdateEnd + :mgnyear")
                   ->setParameter(':mgnyear', self::MARGINYEAR)
                   ->setParameter(':qyear', $year);
                // $qb->andWhere("era_domstift.eraStart - :mgnyear < :qyear AND :qyear < era_domstift.eraEnd + :mgnyear")
                //    ->setParameter(':mgnyear', self::MARGINYEAR)
                //    ->setParameter(':qyear', $year);
            }

            # do not handle triple combinations with place:
            # if domstift != place the result set will be empty or very small

        }
        # office
        elseif ($office) {
            $qb->join('co.officelookup', 'olt_office')
               ->andWhere('olt_office.officeName LIKE :office')
               ->setParameter('office', '%'.$office.'%');
            # office - year
            if ($year) {
                $qb->andWhere("olt_office.numdateStart - :mgnyear < :qyear ".
                              " AND :qyear < olt_office.numdateEnd + :mgnyear")
                   ->setParameter(':mgnyear', self::MARGINYEAR)
                   ->setParameter(':qyear', $year);
            }
        }
        # year
        elseif ($year) {
            # there is an entry in cn_era (domstift = 'all') for each canon
            # eraStart is not NULL if eraEnd is not NULL (and vice versa)
            $qb->join('co.era', 'era')
               ->andWhere("era.domstift = 'all'")
               ->andWhere("era.eraStart - :mgnyear < :qyear AND :qyear < era.eraEnd + :mgnyear")
               ->setParameter(':mgnyear', self::MARGINYEAR)
               ->setParameter(':qyear', $year);
        }

        # place
        if ($place) {
            $qb->join('co.officelookup', 'olt_place')
               ->andWhere('olt_place.locationName LIKE :place OR olt_place.archdeaconTerritory LIKE :place')
               ->setParameter('place', '%'.$place.'%');

            # place - office
            if ($office) {
                $qb->andWhere('olt_place.officeName LIKE :office')
                   ->setParameter('office', '%'.$office.'%');

                # domstift - office - year
                if ($year) {
                    $qb->andWhere("olt_place.numdateStart - :mgnyear < :qyear ".
                                  " AND :qyear < olt_place.numdateEnd + :mgnyear")
                       ->setParameter(':mgnyear', self::MARGINYEAR)
                       ->setParameter(':qyear', $year);
                }
            }
            # domstift - year
            elseif ($year) {
                $qb->andWhere("olt_place.numdateStart - :mgnyear < :qyear ".
                              " AND :qyear < olt_place.numdateEnd + :mgnyear")
                   ->setParameter(':mgnyear', self::MARGINYEAR)
                   ->setParameter(':qyear', $year);
            }
        }

        # names
        if ($name) {
            $qb->join('co.namelookup', 'nlt')
               ->andWhere("nlt.givenname LIKE :qname OR nlt.familyname LIKE :qname".
                          " OR nlt.gn_fn LIKE :qname OR nlt.gn_prefix_fn LIKE :qname")
               ->setParameter('qname', '%'.$name.'%');
        }

        # identifier
        if ($someid) {
            $qb->leftJoin('co.idlookup', 'ilt')
               ->andWhere('ilt.authorityId LIKE :someid_pat '.
                          'OR co.wiagid LIKE :someid_pat '.
                          'OR co.id_dh = :someid '.
                          'OR co.id_gs = :someid')
               ->setParameter(':someid_pat', '%'.$someid.'%')
               ->setParameter(':someid', $someid);
        }

        $this->addFacets($formmodel, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    /**
     * add conditions set by facets
     */
    public function addFacets($querydata, $qb) {
        if ($querydata->facetLocations) {
            $locations = array_column($querydata->facetLocations, 'id');
            $qb->join('co.officelookup', 'ocfctl')
               ->andWhere('ocfctl.locationName IN (:locations)')
               ->setParameter('locations', $locations);
        }
        if ($querydata->facetMonasteries) {
            $ids_monastery = array_column($querydata->facetMonasteries, 'id');
            // $facetMonasteries = array_map(function($a) {return 'Domstift '.$a;}, $facetMonasteries);
            $qb->join('co.officelookup', 'ocfctp')
               ->join('ocfctp.monastery', 'mfctp')
               ->andWhere('mfctp.wiagid IN (:places)')
               ->setParameter('places', $ids_monastery);
        }
        if ($querydata->facetOffices) {
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
        if ($monastery) $sort = 'domstift_domstift';

        // domstift via facet
        if ($bishopquery->isEmpty() and $bishopquery->facetMonasteries) {
            $fctMon = $bishopquery->facetMonasteries;
            $domstiftSrt = array();
            foreach($bishopquery->facetMonasteries as $mon) {
                $domstiftSrt[] = $mon->getName();
            }
            $sort = 'domstift_facet';
        }

        /**
         * a reliable order is required
         */
        switch ($sort) {
        case 'domstift_facet':
            $qb->join('co.era', 'era_srt', 'WITH', 'era_srt.domstift in (:domstiftSrt)')
               ->setParameter(':domstiftSrt', $domstiftSrt)
               ->addOrderBy('era_srt.domstift', 'ASC')
               ->addOrderBy('era_srt.eraStart', 'ASC')
               ->addOrderBy('era_srt.eraEnd', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'domstift_domstift':
            // join see conditions
            $qb->addOrderBy('era_domstift_srt.domstift', 'ASC')
               ->addOrderBy('era_domstift_srt.eraStart', 'ASC')
               ->addOrderBy('era_domstift_srt.eraEnd', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'year':
            // join see conditions
            $qb->addOrderBy('era.eraStart', 'ASC')
               ->addOrderBy('era.eraEnd', 'ASC')
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'name':
            // join('co.era', 'era', 'ASC')
            $qb->join('co.era', 'era', 'WITH', "era.domstift = 'all'")
               ->addOrderBy('co.familyname', 'ASC')
               ->addOrderBy('co.givenname', 'ASC')
               ->addOrderBy('era.eraStart', 'ASC')
               ->addOrderBy('co.id');
            break;
        case 'domstift':
            $qb->join('co.era', 'era_srt', 'WITH', "era_srt.domstift <> 'all'")
               ->addOrderBy('era_srt.domstift')
               ->addOrderBy('era_srt.eraStart')
               ->addOrderBy('era_srt.eraEnd')
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
        if ($mon) {
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

        $qb->groupBy('domstift.name', 'domstift.gsId');

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

    /**
     * Fill object with data for the list view.
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

    /**
     * Fill object with data for the detail view.
     */
    public function fillData(CnOnline $online, $monasteryName = null) {
        // this looks not very elegant, but it is simple and each step is easy to control
        $em = $this->getEntityManager();
        // DH
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
        // GS only
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

        // order offices by $monasteryName if $monasteryName is given (print)
        $co = $online->getCanonDh();
        if ($co) {
            $offices = $co->getOffices();
            $co->setOffices($this->prioByMonastery($offices, $monasteryName));
        }
        $co = $online->getCanonGs();
        if ($co) {
            $offices = $co->getOffices();
            $co->setOffices($this->prioByMonastery($offices, $monasteryName));
        }
        $co = $online->getBishop();
        if ($co) {
            $offices = $co->getOffices();
            $co->setOffices($this->prioByMonastery($offices, $monasteryName));
        }
    }

    public function fillGSOfficesAndReferences(CnOnline $online) {
        $em = $this->getEntityManager();
        $officesgs = $em->getRepository(CnOfficeGS::class)->findByIdCanonAndSort($online->getIdGs());
        $online->setOfficesGs($officesgs);

        $refsrepogs = $em->getRepository(CnCanonReferenceGS::class);
        $refsgs = $refsrepogs->findByIdCanon($online->getIdGs(), ['idReference' => 'ASC']);
        $online->setReferencesGs($refsgs);
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

    public function findOneByIdDh($value): ?CnOnline {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.id_dh = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findOneByIdGs($value): ?CnOnline {
        return $this->createQueryBuilder('c')
                    ->andWhere('c.id_gs = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findOneByIdEp($value): ?CnOnline {
        $result = $this->createQueryBuilder('c')
                    ->andWhere('c.id_ep = :val')
                    ->setParameter('val', $value)
                    ->getQuery()
                    ->getResult();

        // There should at most be one element in the result. Do not crash if there are several elements.
        return $result ? $result[0] : null;
    }

    public function findOffices($idMonastery) {
        $qb = $this->createQueryBuilder('c')
                   ->select('o.officeName')
                   ->join('c.officelookup', 'ofs')
                   ->andWhere('ofs.idMonastery = :id')
                   ->setParameter(':id', $idMonastery)
                   ->join('c.officelookup', 'o')
                   ->groupBy('o.officeName');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function findByMonasteryOffice($domstift, $officeName) {
        $qb = $this->createQueryBuilder('co')
                   ->join('co.officelookup', 'mns')
                   ->andWhere('mns.domstift = :domstift')
                   ->setParameter(':domstift', $domstift)
                   ->andWhere('mns.officeName = :officeName')
                   ->setParameter(':officeName', $officeName)
                   ->join('co.era', 'era_srt')
                   ->andWhere('era_srt.domstift LIKE :domstift')
                   ->addOrderBy('era_srt.eraStart', 'ASC')
                   ->addOrderBy('era_srt.eraEnd', 'ASC')
                   ->addOrderBy('co.familyname', 'ASC')
                   ->addOrderBy('co.givenname', 'ASC')
                   ->addOrderBy('co.id');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * prioByMonastery($monasteryName)
     *
     * put offices related to `$monasteryName` on top
     */
    public function prioByMonastery(Collection $offices, $monasteryName) {
        if (is_null($monasteryName) || is_null($offices)) {
            return $offices;
        }

        $em = $this->getEntityManager();
        $domstift = $em->getRepository(Domstift::class)->findOneByName($monasteryName);
        if (is_null($domstift)) {
            return $offices;
        }
        $idDomstift = $domstift->getGsId();

        $partOffices = $offices->partition(function($key, $value) use ($idDomstift) {
            return $value->getIdMonastery() == $idDomstift;
        });

        if (count($partOffices) > 1) {
            foreach ($partOffices[1] as $oom) {
                $partOffices[0]->add($oom);
            }
        }

        return $partOffices[0];
    }


}
