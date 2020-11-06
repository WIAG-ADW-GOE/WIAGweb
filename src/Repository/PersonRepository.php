<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository {

    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 50;

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

    /* AJAX callback function */
    public function suggestNameObsolete($name, $limit = 40): array {

        // get suggestion directly from the lookup table 2020-10-27
        $qb = $this->createQueryBuilder('p')
                   ->select("DISTINCT CASE WHEN p.prefix_name <> '' THEN CONCAT(p.givenname, ' ', p.prefix_name, ' ', p.familyname) ELSE CONCAT(p.givenname, ' ', p.familyname)END as suggestion")
                   ->join('p.namelookup', 'nlt')
                   ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefix_name, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname")
                   ->setParameter('qname', '%'.$name.'%')
                   ->setMaxResults($limit);

        $suggestions = $qb->getQuery()->getResult();

        return $suggestions;

        //     $sql = "SELECT DISTINCT(familyname) as suggestion FROM familynamevariant".
        //          " WHERE familyname like '%{$name}%'".
        //          " UNION SELECT DISTINCT(givenname) as suggestion FROM givennamevariant".
        //          " WHERE givenname like '%{$name}%' LIMIT $limiti";
    }

    public function addFacets($querydata, $qb) {
        if($querydata->facetPlaces) {
            $facetdioceses = array();
            foreach($querydata->facetPlaces as $d) {
                $facetdioceses[] = $d->name;
            }
            $qb->join('person.offices', 'ocfctp')
               ->andWhere("ocfctp.diocese IN (:facetdioceses)")
               ->setParameter('facetdioceses', $facetdioceses);
        }
        if($querydata->facetOffices) {
            $facetoffices = array();
            foreach($querydata->facetOffices as $d) {
                $facetoffices[] = $d->name;
            }
            $qb->join('person.offices', 'ocfctoc')
               ->andWhere("ocfctoc.office_name IN (:facetoffices)")
               ->setParameter('facetoffices', $facetoffices);
        }

        return $qb;
    }

    public function countByQueryObject(BishopQueryFormModel $bishopquery) {
        if($bishopquery->isEmpty()) return 0;

        $qb = $this->createQueryBuilder('person')
                   ->select('COUNT(DISTINCT person.wiagid)');

        $this->addQueryConditions($qb, $bishopquery);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findWithOffices(BishopQueryFormModel $bishopquery, $limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('person')
                   ->join('person.offices', 'oc')
                   ->addSelect('oc')
                   ->join('oc.numdate', 'ocdatecmp')
                   ->addSelect('ocdatecmp');

        $this->addQueryConditions($qb, $bishopquery);


        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());

        $this->addSortParameter($qb, $bishopquery);

        $query = $qb->getQuery();

        $persons = new Paginator($query, true);

        // $persons = $query->getResult();

        return $persons;
    }


    public function addQueryConditions($qb, BishopQueryFormModel $bishopquery) {

        # identifier
        if($bishopquery->someid) {
            $qb->andWhere(":someid = person.wiagid".
                          " OR :someid = person.gsid".
                          " OR :someid = person.viafid".
                          " OR :someid = person.wikidataid".
                          " OR :someid = person.gndid")
               ->setParameter(':someid', $bishopquery->someid);
        }

        # year
        if($bishopquery->year) {
            $erajoined = true;
            $qb->join('person.era', 'era')
                ->andWhere('era.era_start - :mgnyear < :qyear AND :qyear < era.era_end + :mgnyear')
                ->setParameter(':mgnyear', self::MARGINYEAR)
                ->setParameter(':qyear', $bishopquery->year);
        }

        # office title
        if($bishopquery->office) {
            // we have to join office a second time to filter at the level of persons
            $qb->join('person.offices', 'octitle')
                ->andWhere('octitle.office_name LIKE :office')
                ->setParameter('office', '%'.$bishopquery->office.'%');
        }

        # office diocese
        if($bishopquery->place) {
            // we have to join office a second time to filter at the level of persons
            $sort = 'yearatplace';
            $qb->join('person.offices', 'ocplace')
                ->andWhere('ocplace.diocese LIKE :place')
                ->setParameter('place', '%'.$bishopquery->place.'%');
        }
        # names
        if($bishopquery->name) {
            $qb->join('person.namelookup', 'nlt')
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefix_name, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->setParameter('qname', '%'.$bishopquery->name.'%');
        }

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
            if(!$bishopquery->year) {
                $qb->join('person.era', 'era');
            }
            $qb->orderBy('era.era_start, person.givenname');
            break;
        case 'yearatplace':
            $qb->join('ocplace.numdate', 'ocplacedate')
               ->orderBy('ocplacedate.date_start, person.givenname', 'ASC');
            break;
        case 'name':
            $qb->orderBy('person.familyname, person.givenname, oc.diocese');
            break;
        }

        return $qb;

    }

    public function findOneWithOffices($wiagid) {
        // fetch all data related to this person
        $query = $this->createQueryBuilder('person')
                      ->andWhere('person.wiagid = :wiagid')
                      ->setParameter('wiagid', $wiagid)
                      ->leftJoin('person.offices', 'oc')
                      ->addSelect('oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.date_start', 'ASC')
                      ->leftJoin('oc.monastery', 'monastery')
                      ->addSelect('monastery')
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
        // The QueryBuilder joins to 'monastery' twice!?
        // $qb = $this->getEntityManager()
        //            ->createQueryBuilder();
        // foreach($person->getOffices() as $oc) {
        //     if($oc->getIdMonastery()) {
        //         $ocid = $oc->getWiagid();
        //         $qb->select('place.place_name')
        //            ->from('App\Entity\Office', 'oc')
        //            ->join('oc.monastery',  'monastery')
        //            ->join('monastery.locations', 'locations')
        //            ->join('locations.place', 'place')
        //            ->andWhere('oc.wiagid = :ocid')
        //            ->setParameter('ocid', $ocid);

        //         $query = $qb->getQuery();
        //         $qrplacenames = $query->getResult();
        //         $placenames = array_map(
        //             function($el) {
        //                 return $el['place_name'];
        //             },
        //             $qrplacenames
        //         );
        //         $oc->setMonasteryplacestr(implode(', ', $placenames));
        //     }
        // }

        $em = $this->getEntityManager();
        $officeRepository = $em->getRepository(Office::class);
        foreach($person->getOffices() as $oc) {
            $officeRepository->setMonasteryLocation($oc);
        }
    }

}
