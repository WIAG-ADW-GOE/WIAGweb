<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Office;
use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOnline;
use App\Entity\CnOffice;
use App\Entity\CnOfficeGS;
use App\Entity\CnCanonReference;
use App\Entity\CnCanonReferenceGS;
use App\Form\Model\BishopQueryFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository {

    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    // /**
    //  * @return Person[] Returns an array of Person objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * add conditions set by facets
     */
    public function addFacets($querydata, $qb) {
        if($querydata->facetPlaces) {
            $facetPlaces = array_column($querydata->facetPlaces, 'name');
            $qb->join('person.offices', 'ocfctp')
               ->andWhere("ocfctp.diocese IN (:dioceses)")
               ->setParameter('dioceses', $facetPlaces);
        }
        if($querydata->facetOffices) {
            $facetOffices = array_column($querydata->facetOffices, 'name');
            $qb->join('person.offices', 'ocfctoc')
               ->andWhere("ocfctoc.office_name IN (:offices)")
               ->setParameter('offices', $facetOffices);
        }

        return $qb;
    }

    public function countByQueryObject(BishopQueryFormModel $bishopquery) {
        if($bishopquery->isEmpty()) return array(1 => 0);

        $qb = $this->createQueryBuilder('person')
                   ->select('COUNT(DISTINCT person.wiagid)');

        $this->addQueryConditions($qb, $bishopquery);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findWithOffices(
        ?BishopQueryFormModel $bishopquery,
        $limit = 0,
        $offset = 0,
        $addMonasteryLocations = false) {

        $qb = $this->createQueryBuilder('person')
                   ->leftJoin('person.offices', 'oc')
                   ->addSelect('oc')
                   ->leftJoin('oc.numdate', 'ocdatecmp')
                   ->addSelect('ocdatecmp');

        if (!is_null($bishopquery)) {
            $this->addQueryConditions($qb, $bishopquery);
            $this->addSortParameter($qb, $bishopquery);
        }

        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());

        $query = $qb->getQuery();

        $persons = new Paginator($query, true);

        // 2021-11-26: obsolete: see Office.locationShow
        // if ($addMonasteryLocations) {
        //     foreach($persons as $p) {
        //         if($p->hasMonastery()) {
        //             $this->addMonasteryLocation($p);
        //         }
        //     }
        // }

        // $persons = $query->getResult();

        return $persons;
    }


    private function addQueryConditions(QueryBuilder $qb, BishopQueryFormModel $bishopquery): QueryBuilder {

        # identifier
        if($bishopquery->someid && $bishopquery->someid != "") {
            $db_id = Person::extractDbId($bishopquery->someid);
            $id_param = $db_id ? $db_id : $bishopquery->someid;

            $qb->andWhere(":someid = person.wiagid".
                          " OR :someid = person.gsid".
                          " OR :someid = person.viafid".
                          " OR :someid = person.wikidataid".
                          " OR :someid = person.gndid")
               ->setParameter(':someid', $id_param);
        }

        # year
        if($bishopquery->year && $bishopquery->year != "") {
            $erajoined = true;
            $qb->join('person.era', 'era')
                ->andWhere('era.era_start - :mgnyear < :qyear AND :qyear < era.era_end + :mgnyear')
                ->setParameter(':mgnyear', self::MARGINYEAR)
                ->setParameter(':qyear', $bishopquery->year);
        }

        # office title
        if($bishopquery->office && $bishopquery->office != "") {
            // we have to join office a second time to filter at the level of persons
            $qb->join('person.offices', 'octitle')
                ->andWhere('octitle.office_name LIKE :office')
                ->setParameter('office', '%'.$bishopquery->office.'%');
        }

        # office diocese
        if($bishopquery->place && $bishopquery->place != "") {
            // we have to join office a second time to filter at the level of persons
            $sort = 'yearatplace';
            $qb->join('person.officeSortkeys', 'ocselectandsort')
                ->andWhere('ocselectandsort.diocese LIKE :place')
                ->setParameter('place', '%'.$bishopquery->place.'%');
        }

        # names
        if($bishopquery->name && $bishopquery->name != "") {
            $qb->join('person.namelookup', 'nlt')
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefix_name, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->setParameter('qname', '%'.$bishopquery->name.'%');
        }
        // dump($qb);

        $this->addFacets($bishopquery, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    public function addSortParameter($qb, $bishopquery) {

        $sort = 'name';
        if($bishopquery->year || $bishopquery->office) $sort = 'year';
        if($bishopquery->place) $sort = 'yearatplace';
        if($bishopquery->name) $sort = 'name';

        /**
         * a reliable order is required, therefore person.givenname shows up
         * in each sort clause
         */

        switch($sort) {
        case 'year':
            // if(!$bishopquery->year) {
            //     $qb->join('person.era', 'era');
            // }
            // $qb->orderBy('era.era_start, person.givenname');
            $qb->leftJoin('person.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, person.givenname, person.wiagid');
            break;
        case 'yearatplace':
            // $qb->join('ocplace.numdate', 'ocplacedate')
            //    ->orderBy('ocplacedate.date_start, person.givenname', 'ASC');
            $qb->orderBy('ocselectandsort.sortkey, person.givenname, person.wiagid');
            break;
        case 'name':
            // $qb->orderBy('person.familyname, person.givenname, oc.diocese');
            $qb->leftJoin('person.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, person.givenname, person.wiagid');
            break;
        }

        return $qb;

    }

    public function findOneWithOffices($wiagid) {
        $db_id = Person::extractDbId($wiagid);
        $id_param = $db_id ? $db_id : $wiagid;
        // fetch all data related to this person
        $query = $this->createQueryBuilder('person')
                      ->andWhere('person.wiagid = :id')
                      ->setParameter('id', $id_param)
                      ->leftJoin('person.offices', 'oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.date_start', 'ASC')
                      ->leftJoin('oc.monastery', 'monastery')
                      ->getQuery();

        $person = $query->getOneOrNullResult();

        return $person;
    }

    public function findOfficeNames(BishopQueryFormModel $bishopquery) {
        $qb = $this->createQueryBuilder('person')
                   ->select('DISTINCT oc.office_name, COUNT(DISTINCT(person.wiagid)) as n')
                   ->join('person.offices', 'oc');

        $this->addQueryConditions($qb, $bishopquery);

        $qb->groupBy('oc.office_name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * return list of places, where persons have an office;
     * used for the facet of places
     */
    public function findOfficePlaces(BishopQueryFormModel $bishopquery) {
        $qb = $this->createQueryBuilder('person')
                   ->select('DISTINCT oc.diocese, COUNT(DISTINCT(person.wiagid)) as n')
                   ->join('person.offices', 'oc')
                   ->andWhere("oc.diocese <> ''");

        $this->addQueryConditions($qb, $bishopquery);

        $qb->groupBy('oc.diocese');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function findtest($id) {
        $query = $this->getEntityManager()
                      ->createQuery("SELECT p FROM App\Entity\Person p JOIN".
                                    " (SELECT px FROM App\Entity\Person px WHERE px.wiagid = :id) st".
                                    " ON st.wiagd = p.wiagid")
                      ->setParameter('id', $id);

        $person = $query->getResult();
        return($person);
    }

    public function addMonasteryLocation(Person $person) {
        $em = $this->getEntityManager();
        $officeRepository = $em->getRepository(Office::class);
        foreach($person->getOffices() as $oc) {
            $officeRepository->setMonasteryLocation($oc);
        }
    }

    public function findAllGnds($limit = 0, $offset = 0) {
        $qb = $this->createQueryBuilder('p')
                   ->select('DISTINCT p.gndid')
                   ->where('p.gndid is not null');

        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;

    }

    /** AJAX callback */
    public function suggestId($input, $limit = 200): array {
        $qb = $this->createQueryBuilder('p')
                   ->select('p.wiagid AS suggestion')
                   ->andWhere('CONCAT (:prefix, p.wiagid, :postfix) LIKE :input')
                   ->setParameter('input', '%'.$input.'%')
                   ->setParameter('prefix', Person::WIAGID_PREFIX)
                   ->setParameter('postfix', Person::WIAGID_POSTFIX)
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        return $query->getResult();

    }



}
