<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        $conn = $this->getEntityManager()->getConnection();

        /* TODO
         * - ORDER BY p.familyname ASC
         * - [X] include name variants
         */

        $concat = "CONCAT_WS(' ', p.givenname, p.prefix_name, p.familyname)";

        $sql = "SELECT DISTINCT({$concat}) as suggestion FROM person p".
             " WHERE {$concat} LIKE '%{$name}%' LIMIT $limit";

        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute();
        $sqlres = $stmt->fetchAll();

        if (count($sqlres) < $limit) {
            $limiti = $limit - count($sqlres);
            $sql = "SELECT DISTINCT(familyname) as suggestion FROM familynamevariant".
                 " WHERE familyname like '%{$name}%'".
                 " UNION SELECT DISTINCT(givenname) as suggestion FROM givennamevariant".
                 " WHERE givenname like '%{$name}%' LIMIT $limiti";
            $stmt = $conn->prepare($sql);
            // is it possible to reuse prepared statements?
            $stmt->execute();
            $sqlres = array_merge($sqlres, $stmt->fetchAll());
        }

        return $sqlres;
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
        if ($qd->name) {
            $condname = "CONCAT_WS(' ', person.givenname, person.prefix_name, person.familyname) LIKE '%{$qd->name}%'";
            $select = array();
            $select[] = "SELECT wiagid FROM person WHERE {$condname}";
            $select[] = "UNION SELECT wiagid FROM person WHERE CONCAT_WS(' ', givenname, familyname) LIKE '%{$qd->name}%'";
            $select[] = "UNION SELECT wiagid from familynamevariant WHERE familyname LIKE '%{$qd->name}%'";
            $select[] = "UNION SELECT wiagid from givennamevariant WHERE givenname LIKE '%{$qd->name}%'";
            if ($fextended) {
                $nameelts = explode(" ", $qd->name);
                if (count($nameelts) > 1) {
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

        if ($qd->place) {
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.diocese like '%{$qd->place}%') as t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->facetPlaces) {

            $dioceses = array();
            foreach($qd->facetPlaces as $d) {
                $dioceses[] = "'{$d->name}'";
            }
            $set_of_dioceses = implode(", ", $dioceses);

            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.diocese IN ({$set_of_dioceses})) AS t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->office) {
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.office_name like '%{$qd->office}%') AS t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->facetOffices) {
            $offices = array();
            foreach($qd->facetOffices as $oc) {
                $offices[] = "'{$oc->name}'";
            }
            $set_of_offices = implode(", ", $offices);
            $csqlid[] = "(SELECT wiagid_person as wiagid FROM office".
                      " WHERE office.office_name IN ({$set_of_offices})) AS t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->year) {
            $mgnyear = self::MARGINYEAR;
            $csqlid[] = "(SELECT wiagid_person as wiagid, era_start, era_end FROM era".
                      " WHERE (era_start - {$mgnyear} < {$qd->year} AND {$qd->year} < era_end + {$mgnyear})) as t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->someid) {
            $condsomeid = "'{$qd->someid}' IN (person.gsid, person.gndid, person.viafid, person.wikidataid, person.wiagid)";
            $csqlid[] = "(SELECT wiagid FROM person".
                      " WHERE {$condsomeid}) as t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        $sqlwhere = $tno > 2 ? " WHERE ".join(' AND ', $csqlwh) : "";
        $sql = "(SELECT DISTINCT(t1.wiagid) as wiagid FROM ".join(', ', $csqlid).$sqlwhere.") AS twiagid";

        return $sql;

    }

    public function countByQueryObject(BishopQueryFormModel $querydata) {

        if ($querydata->isEmpty()) return 0;

        // check if we got a wiagid with prefix and suffix
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(twiagid.wiagid) AS count FROM ".
             $this->buildWiagidSet($querydata);

        $stmt = $conn->prepare($sql);
        $sqlres = $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByQueryObject(BishopQueryFormModel $querydata, $limit, $page): array {
        $conn = $this->getEntityManager()->getConnection();

        $offset = ($page - 1) * $limit;

        # ## TODO do this query with DQL
        $sql = "SELECT person.* FROM person, ".
             $this->buildWiagidSet($querydata).
             " WHERE person.wiagid = twiagid.wiagid";
        if($limit > 0) $sql = $sql." LIMIT {$limit} OFFSET {$offset}";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findByPlaceWithOffices(BishopQueryFormModel $querydata, $limit, $page) {
        # ## TODO observe facets

        $puresql = false;
        if($puresql) {
            $sql = "SELECT pn.* FROM person as pn, office as oce, officedate as ocedate".
                 " WHERE oce.diocese LIKE :place".
                 " AND oce.wiagid = ocedate.wiagid_office ".
                 " AND pn.wiagid = oce.wiagid_person".
                 " ORDER BY ocedate.date_start ASC";
                
        $offset = ($page - 1) * $limit;
        if($limit > 0) $sql = $sql." LIMIT {$limit} OFFSET {$offset}";

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute(['place' => '%'.$querydata->place.'%']);

        // returns an array of arrays (i.e. a raw data set)
        $persons_raw = $stmt->fetchAll();

        $persons = $this->getObjects($persons_raw);
        } else {
            $puredql = false;
            if($puredql) {
                $dql = "SELECT pn FROM App\Entity\Person as pn".
                     " JOIN App\Entity\Office as oce".
                     " JOIN App\Entity\Officedate as ocedate".
                     " WHERE oce.diocese LIKE :place".
                     " AND pn.wiagid = oce.wiagid_person".
                     " AND oce.wiagid = ocedate.wiagid_office".
                     " ORDER BY ocedate.date_start ASC";

            
            $query = $this->getEntityManager()->createQuery($dql);
            $query->setParameter('place', '%'.$querydata->place.'%');
            } else {
                $query = $this->createQueryBuilder('person')
                              ->leftJoin('person.offices', 'oc')
                              ->addSelect('oc')
                              ->andWhere('oc.diocese LIKE :place')
                              ->leftJoin('oc.numdate', 'ocdate')
                              ->orderBy('ocdate.date_start', 'ASC')
                              ->setParameter('place', '%'.$querydata->place.'%')
                              ->getQuery();
            }

            if($limit > 0) {
                $offset = ($page - 1) * $limit;
                $query->setMaxResults($limit);
                $query->setFirstResult($offset);
            }

            $persons = $query->getResult();
            
            // $ocRep = $this->getEntityManager()->getRepository(Office::class);
            
            // foreach($persons as $person) {
            //     $offices = $ocRep->findByIDPerson($person->getWiagid());      
            //     $person->setOffices($offices);
            // }
        }

        return $persons;
    }

    public function findWiagidByQueryObject(BishopQueryFormModel $querydata, $limit, $page) {
        $conn = $this->getEntityManager()->getConnection();

        $offset = ($page - 1) * $limit;

        $sql = "SELECT person.wiagid FROM person, ".
             $this->buildWiagidSet($querydata).
             " WHERE person.wiagid = twiagid.wiagid";
        if($limit > 0) $sql = $sql." LIMIT {$limit} OFFSET {$offset}";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // returns an array of arrays (i.e. a raw data set)
        $sqlres = $stmt->fetchAll();

        return $sqlres;
    }

    public function findObjectsByQueryObject(BishopQueryFormModel $querydata, $limit = 0, $page = 0): array {
        # ## TODO does this work?
        $wiagids = $this->findWiagidByQueryObject($querydata, $limit, $page);

        $strWiagids = implode(",", array_map(function($v) {return $v['wiagid'];}, $wiagids));

        $persons = $this->createQueryBuilder('p')
                        ->andWhere('p.wiagid in ('.$strWiagids.')')
                        ->getQuery()
                        ->getResult();
        foreach($persons as $p) {
            $p->setOffices($this->findOfficeByWiagid($p->getWiagid()));
        }
        return $persons;
    }

    public function findOfficeByWiagid_obsolete(string $wiagid) {
        $conn = $this->getEntityManager()->getConnection();

        // TODO use OfficeRepository
        $ocRep = $this->getEntityManager()->getRepository(Office::class);

        return $ocRep->createQueryBuilder('o')
                     ->andWhere("o.wiagid_person = {$wiagid}")
                     ->getQuery()
                     ->getResult();
    }


    public function findWithOffices(BishopQueryFormModel $bishopquery, $limit = 0, $page = 0) {
        $persons_raw = $this->findByQueryObject($bishopquery, $limit, $page);

        $persons = $this->getObjects($persons_raw);
        return $persons;
    }

    public function getObjects($persons_raw) {
        $ocRep = $this->getEntityManager()->getRepository(Office::class);
        
        $persons = array();
        foreach($persons_raw as $p) {
            $person = new Person();
            $person->setFields($p);
            $offices = $ocRep->findByIDPerson($person->getWiagid());            
            $person->setOffices($offices);
            $persons[] = $person;
        }
        return $persons;
    }   

    public function findOneWithOffices($wiagid) {
        // fetch all data related to this person
        $query = $this->createQueryBuilder('person')
                      ->andWhere('person.wiagid = :wiagid')
                      ->setParameter('wiagid', $wiagid)
                      ->leftJoin('person.familyname_variant', 'fnv')
                      ->addSelect('fnv')
                      ->leftJoin('person.givenname_variant', 'gnv')
                      ->addSelect('gnv')
                      ->leftJoin('person.offices', 'oc')
                      ->addSelect('oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.date_start', 'ASC')
                      ->getQuery();

        $person = $query->getOneOrNullResult();

        return $person;
    }

}
