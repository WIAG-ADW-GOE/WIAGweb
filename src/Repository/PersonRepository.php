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
    public function suggestName($name, $limit = 40): array {

        $qb = $this->createQueryBuilder('p')
                   ->select("DISTINCT CASE WHEN p.prefix_name <> '' THEN CONCAT(p.givenname, ' ', p.prefix_name, ' ', p.familyname) ELSE CONCAT(p.givenname, ' ', p.familyname)END as suggestion")
                   ->join('p.namelookup', 'nlt')
                   ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefix_name, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname")
                   ->setParameter('qname', '%'.$name.'%')
                   ->setMaxResults($limit);

        $suggestions = $qb->getQuery()->getResult();

        # dd(array_map(function($a) {return $a['suggestion'];}, $suggestions));
        return $suggestions;

        // $conn = $this->getEntityManager()->getConnection();

        // /* TODO
        //  * - ORDER BY p.familyname ASC
        //  * - [X] include name variants
        //  */

        // $concat = "CONCAT_WS(' ', p.givenname, p.prefix_name, p.familyname)";

        // $sql = "SELECT DISTINCT({$concat}) as suggestion FROM person p".
        //      " WHERE {$concat} LIKE '%{$name}%' LIMIT $limit";

        // $stmt = $conn->prepare($sql);
        // // is it possible to reuse prepared statements?
        // $stmt->execute();
        // $sqlres = $stmt->fetchAll();

        // if(count($sqlres) < $limit) {
        //     $limiti = $limit - count($sqlres);
        //     $sql = "SELECT DISTINCT(familyname) as suggestion FROM familynamevariant".
        //          " WHERE familyname like '%{$name}%'".
        //          " UNION SELECT DISTINCT(givenname) as suggestion FROM givennamevariant".
        //          " WHERE givenname like '%{$name}%' LIMIT $limiti";
        //     $stmt = $conn->prepare($sql);
        //     // is it possible to reuse prepared statements?
        //     $stmt->execute();
        //     $sqlres = array_merge($sqlres, $stmt->fetchAll());
        // }

        // // dd($sqlres);
        // return $sqlres;
    }


    public function findByFamilyname($name, $limit = 1000): array {
        $conn = $this->getEntityManager()->getConnection();

        //         ORDER BY p.familyname ASC

        $sql = "SELECT * FROM person p
        WHERE p.familyname LIKE :name
        LIMIT $limit";

        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute([
            'name' => "%{$name}%",
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * return a SQL subquery which yields a list of wiagids that fullfill all
     * conditions in `$qd`.
     * Decide about a search strategy and set `$fextended` eventually to true in calls to `buildWiagidSet`.
     */
    public function buildWiagidSet(BishopQueryFormModel $qd, $fextended = false) {

        $csqlid = array();
        $csqlwh = array();
        $tno = 1;
        if($qd->name) {
            $condname = "CONCAT_WS(' ', person.givenname, person.prefix_name, person.familyname) LIKE '%{$qd->name}%'";
            $select = array();
            $select[] = "SELECT wiagid FROM person WHERE {$condname}";
            $select[] = "UNION SELECT wiagid FROM person WHERE CONCAT_WS(' ', givenname, familyname) LIKE '%{$qd->name}%'";
            $select[] = "UNION SELECT wiagid from familynamevariant WHERE familyname LIKE '%{$qd->name}%'";
            $select[] = "UNION SELECT wiagid from givennamevariant WHERE givenname LIKE '%{$qd->name}%'";
            if($fextended) {
                $nameelts = explode(" ", $qd->name);
                if(count($nameelts) > 1) {
                    foreach ($nameelts as $nameelt) {
                        $nameelt = trim($nameelt, ",;. ");
                        $select[] = "UNION SELECT wiagid from person WHERE givenname LIKE '%{$nameelt}%'";
                        $select[] = "UNION SELECT wiagid from person WHERE prefix_name LIKE '%{$nameelt}%'";
                        $select[] = "UNION SELECT wiagid from person WHERE familyname LIKE '%{$nameelt}%'";
                        $select[] = "UNION SELECT wiagid from familynamevariant WHERE familyname LIKE '%{$nameelt}%'";
                        $select[] = "UNION SELECT wiagid from givennamevariant WHERE givenname LIKE '%{$nameelt}%'";
                    }
                }
            }
            $csqlid[] = "(".implode(" ", $select).") as t{$tno}";
            $tno += 1;
        }

        if($qd->place) {
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.diocese like '%{$qd->place}%') as t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if($qd->facetPlaces) {

            $dioceses = array();
            foreach($qd->facetPlaces as $d) {
                $dioceses[] = "'{$d->name}'";
            }
            $set_of_dioceses = implode(", ", $dioceses);

            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.diocese IN ({$set_of_dioceses})) AS t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if($qd->office) {
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.office_name like '%{$qd->office}%') AS t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if($qd->facetOffices) {
            $offices = array();
            foreach($qd->facetOffices as $oc) {
                $offices[] = "'{$oc->name}'";
            }
            $set_of_offices = implode(", ", $offices);
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.office_name IN ({$set_of_offices})) AS t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if($qd->year) {
            $mgnyear = self::MARGINYEAR;
            $csqlid[] = "(SELECT wiagid_person as wiagid, era_start, era_end FROM era".
                      " WHERE (era_start - {$mgnyear} < {$qd->year} AND {$qd->year} < era_end + {$mgnyear})) as t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if($qd->someid) {
            $condsomeid = "'{$qd->someid}' IN (person.gsid, person.gndid, person.viafid, person.wikidataid, person.wiagid)";
            $csqlid[] = "(SELECT wiagid FROM person".
                      " WHERE {$condsomeid}) as t{$tno}";
            if($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        $sqlwhere = $tno > 2 ? " WHERE ".join(' AND ', $csqlwh) : "";
        $sql = "(SELECT DISTINCT(t1.wiagid) as wiagid FROM ".join(', ', $csqlid).$sqlwhere.") AS twiagid";

        return $sql;

    }

    // public function findByQueryObject(BishopQueryFormModel $querydata, $limit, $page): array {
    //     $conn = $this->getEntityManager()->getConnection();

    //     $offset = ($page - 1) * $limit;

    //     # ## TODO do this query with DQL
    //     $sql = "SELECT person.* FROM person, ".
    //          $this->buildWiagidSet($querydata).
    //          " WHERE person.wiagid = twiagid.wiagid";
    //     if($limit > 0) $sql = $sql." LIMIT {$limit} OFFSET {$offset}";

    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute();

    //     return $stmt->fetchAll();
    // }

    // public function findByNameWithOffices(BishopQueryFormModel $querydata, $limit, $page) {
    //     $qb = $this->createQueryBuilder('person')
    //                ->join('person.familyname_variant', 'fnv')
    //                ->join('person.givenname_variant', 'gnv')
    //                ->andWhere('person.familyname LIKE :name'.
    //                           ' OR fnv.familyname LIKE :name')
    //                ->setParameter('name', '%knopf%');

    //     if($limit > 0) {
    //         $offset = ($page - 1) * $limit;
    //         $qb->setMaxResults($limit);
    //         $qb->setFirstResult($offset);
    //     }

    //     $query = $qb->getQuery();
    //     $persons = $query->getResult();
    //     dump($persons);


    //     return $persons;
    // }

    // public function findByPlaceWithOffices(BishopQueryFormModel $querydata, $limit, $page) {
    //     $qb = $this->createQueryBuilder('person')
    //                ->join('person.offices', 'oc')
    //                ->addSelect('oc')
    //                ->join('person.offices', 'ocplace')
    //                ->andWhere('ocplace.diocese LIKE :place');

    //     $this->addFacets($querydata, $qb);

    //     # sort by office date
    //     $qb->join('oc.numdate', 'ocdate')
    //        ->orderBy('ocdate.date_start', 'ASC')
    //        ->setParameter('place', '%'.$querydata->place.'%');

    //     if($limit > 0) {
    //         $offset = ($page - 1) * $limit;
    //         $qb->setMaxResults($limit);
    //         $qb->setFirstResult($offset);
    //     }

    //     $query = $qb->getQuery();
    //     $persons = $query->getResult();

    //     return $persons;
    // }

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


    // public function findWiagidByQueryObject(BishopQueryFormModel $querydata, $limit, $page) {
    //     $conn = $this->getEntityManager()->getConnection();

    //     $offset = ($page - 1) * $limit;

    //     $sql = "SELECT person.wiagid FROM person, ".
    //          $this->buildWiagidSet($querydata).
    //          " WHERE person.wiagid = twiagid.wiagid";
    //     if($limit > 0) $sql = $sql." LIMIT {$limit} OFFSET {$offset}";

    //     $stmt = $conn->prepare($sql);
    //     $stmt->execute();

    //     // returns an array of arrays (i.e. a raw data set)
    //     $sqlres = $stmt->fetchAll();

    //     return $sqlres;
    // }

    // public function findObjectsByQueryObject(BishopQueryFormModel $querydata, $limit = 0, $page = 0): array {
    //     # ## TODO does this work?
    //     $wiagids = $this->findWiagidByQueryObject($querydata, $limit, $page);

    //     $strWiagids = implode(",", array_map(function($v) {return $v['wiagid'];}, $wiagids));

    //     $persons = $this->createQueryBuilder('p')
    //                     ->andWhere('p.wiagid in ('.$strWiagids.')')
    //                     ->getQuery()
    //                     ->getResult();
    //     foreach($persons as $p) {
    //         $p->setOffices($this->findOfficeByWiagid($p->getWiagid()));
    //     }
    //     return $persons;
    // }

    // public function findOfficeByWiagid_obsolete(string $wiagid) {
    //     $conn = $this->getEntityManager()->getConnection();

    //     // TODO use OfficeRepository
    //     $ocRep = $this->getEntityManager()->getRepository(Office::class);

    //     return $ocRep->createQueryBuilder('o')
    //                  ->andWhere("o.wiagid_person = {$wiagid}")
    //                  ->getQuery()
    //                  ->getResult();
    // }


    public function countByQueryObject(BishopQueryFormModel $bishopquery) {
        if($bishopquery->isEmpty()) return 0;

        $qb = $this->createQueryBuilder('person')
                   ->select('COUNT(DISTINCT person.wiagid)');

        $this->addQueryConditions($qb, $bishopquery);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findWithOffices(BishopQueryFormModel $bishopquery, $limit = 0, $page = 0) {
        // $persons_raw = $this->findByQueryObject($bishopquery, $limit, $page);
        // $persons = $this->getObjects($persons_raw);
        // return $persons;

        $qb = $this->createQueryBuilder('person')
                   ->join('person.offices', 'oc')
                   ->addSelect('oc')
                   ->join('oc.numdate', 'ocdatecmp')
                   ->addSelect('ocdatecmp');

        $this->addQueryConditions($qb, $bishopquery);


        if($limit > 0) {
            $offset = ($page - 1) * $limit;
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());

        $sort = null;
        if($bishopquery->year || $bishopquery->office) $sort = 'year';
        if($bishopquery->place) $sort = 'yearatplace';
        if($bishopquery->name) $sort = 'name';

        switch($sort) {
        case 'year':
            if(!$bishopquery->year) {
                $qb->join('person.era', 'era');
            }
            $qb->orderBy('era.era_start');
            break;
        case 'yearatplace':
            $qb->join('ocplace.numdate', 'ocplacedate')
               ->orderBy('ocplacedate.date_start', 'ASC');
            break;
        case 'name':
            $qb->orderBy('person.familyname, person.givenname, oc.diocese');
            break;
        }


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
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname")
               ->setParameter('qname', '%'.$bishopquery->name.'%');
        }

        $this->addFacets($bishopquery, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    // public function getObjects($persons_raw) {
    //     $ocRep = $this->getEntityManager()->getRepository(Office::class);

    //     $persons = array();
    //     foreach($persons_raw as $p) {
    //         $person = new Person();
    //         $person->setFields($p);
    //         $offices = $ocRep->findByIDPerson($person->getWiagid());
    //         $person->setOffices($offices);
    //         $persons[] = $person;
    //     }
    //     return $persons;
    // }

    public function findOneWithOffices($wiagid) {
        // fetch all data related to this person
        $query = $this->createQueryBuilder('person')
                      ->andWhere('person.wiagid = :wiagid')
                      ->setParameter('wiagid', $wiagid)
                      ->leftJoin('person.offices', 'oc')
                      ->addSelect('oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.date_start', 'ASC')
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


}
