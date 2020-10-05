<?php

namespace App\Repository;

use App\Entity\Person;
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
     * return a subquery which yields a list of wiagids that fullfill all
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

        // TODO create table with upper and lower time boundary.
        if ($qd->year) {
            $mgnyear = self::MARGINYEAR;
            $csqlid[] = "(SELECT wiagid_person as wiagid, era_start, era_end FROM era".
                      " WHERE (era_start - {$mgnyear} < {$qd->year} AND {$qd->year} < era_end + {$mgnyear})) as t{$tno}";
            if ($tno > 1) $csqlwh[] = "t1.wiagid = t{$tno}.wiagid";
            $tno += 1;
        }

        if ($qd->someid) {
            $condsomeid = "'{$qd->someid}' IN (person.gsid, person.gndid, person.viafid, person.wiagid)";
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

        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(twiagid.wiagid) AS count FROM ".
             $this->buildWiagidSet($querydata);

        $stmt = $conn->prepare($sql);
        $sqlres = $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findPlacesByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();

        if ($querydata->isEmpty()) {
            $sql = "SELECT DISTINCT(diocese), COUNT(DISTINCT(wiagid_person)) as n FROM office ".
                 " WHERE diocese <> ''".
                 " GROUP BY diocese";
        } else {
            $sql = "SELECT DISTINCT(diocese), COUNT(DISTINCT(wiagid_person)) as n FROM office, ".
                 $this->buildWiagidSet($querydata).
                 " WHERE office.wiagid_person = twiagid.wiagid AND diocese <> ''".
                 " GROUP BY diocese";
        }

        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findOfficesByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "SELECT DISTINCT(office_name), COUNT(DISTINCT(wiagid_person)) as n FROM office, ".
             $this->buildWiagidSet($querydata).
             " WHERE office.wiagid_person = twiagid.wiagid AND diocese <> ''".
             " GROUP BY office_name";

        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }



    public function findByQueryObject(BishopQueryFormModel $querydata, $limit, $page): array {
        $conn = $this->getEntityManager()->getConnection();

        $offset = ($page - 1) * $limit;

        $sql = "SELECT person.* FROM person, ".
             $this->buildWiagidSet($querydata).
             " WHERE person.wiagid = twiagid.wiagid".
             " LIMIT {$limit} OFFSET {$offset}";


        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // returns an array of arrays (i.e. a raw data set)
        $sqlres = $stmt->fetchAll();

        return $sqlres;
    }

    public function findOfficeByWiagid(string $wiagid) {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "SELECT * FROM office WHERE office.wiagid_person = {$wiagid}";

        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function findPersonsAndOffices(BishopQueryFormModel $bishopquery, $limit, $page) {
        $persons = $this->findByQueryObject($bishopquery, $limit, $page);

        $conn = $this->getEntityManager()->getConnection();


        // add offices
        $rawoffices = array();
        $persons_with_offices = array();

        foreach ($persons as $person) {
            $officetexts = array();
            $rawoffices = $this->findOfficeByWiagid($person['wiagid']);
            foreach ($rawoffices as $o) {
                $officetexts[] = $o['office_name'].' ('.$o['diocese'].')';
            }
            // $person['offices'] = $officetexts->toArray();
            $person['offices'] = $rawoffices;
            $persons_with_offices[] = $person;
        }

        return $persons_with_offices;
    }
}
