<?php

namespace App\Repository;

use Ds\Vector;
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
    public function suggestName($name, $limit = 1000): array {
        $conn = $this->getEntityManager()->getConnection();

        //         ORDER BY p.familyname ASC 
        
        $sql = "
        SELECT DISTINCT(CONCAT_WS(' ', p.givenname, p.prefix_name, p.familyname)) as suggestion FROM person p
        WHERE CONCAT_WS(' ', p.givenname, p.prefix_name, p.familyname) LIKE :name
        LIMIT $limit
        ";
        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute([
            'name' => "%{$name}%",
        ]);
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
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

    public function buildWhere(BishopQueryFormModel $querydata): string {

        $tables = new Vector();

        $tables->push("person");

        
        if ($querydata->place or $querydata->facetPlaces) {
            $tables->push("office");
        }

        if ($querydata->name) {
            $tables->push("familynamevariant");
            $tables->push("givennamevariant");
        }


        $sqltables = "person, office, familynamevariant, givennamevariant";

        // dd($tables, $sqltables);

        $condclause = "";
        
        if ($querydata->year) {
            // mysql is quite robust
            $thyear = self::MARGINYEAR;
            $condclause = " ABS(person.date_death - {$querydata->year}) < {$thyear}";
        }
        
        if ($querydata->someid) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." '{$querydata->someid}' IN (person.gsid, person.gndid, person.viafid, person.wiagid)";
        }
        
        if ($querydata->place) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." office.diocese like '%{$querydata->place}%'";
        }

        if ($querydata->facetPlaces) {
            $vp = new Vector($querydata->facetPlaces);
            $set_of_dioceses = $vp->map(function ($pl) {return "'{$pl}'";})->join(", ");
            $condclause = $condclause." AND office.diocese IN ({$set_of_dioceses})";
        }

        if ($querydata->place or $querydata->facetPlaces) {
            $condclause = $condclause." AND person.wiagid = office.wiagid_person";
        }

        if ($querydata->name) {
            $n = $querydata->name;            
            $condclause = $condclause ? $condclause." AND" : "";
            $condname = "CONCAT_WS(' ', person.givenname, person.prefix_name, person.familyname) LIKE '%{$n}%'";
            $condfnvar = "familynamevariant.familyname LIKE '%{$n}%' AND familynamevariant.wiagid = person.wiagid";
            $condgnvar = "givennamevariant.givenname LIKE '%{$n}%' AND givennamevariant.wiagid = person.wiagid";
            $condclause = $condclause."({$condname} OR {$condfnvar} OR {$condgnvar})";
        }

        return " FROM ".$sqltables." WHERE". $condclause;
        
    }

    /**
     * return a subquery which yields a list of wiagids that fullfill all
     * conditions in `$qd`.
     */
    public function buildWiagidSet(BishopQueryFormModel $qd) {

        $csqlid = new Vector();
        $csqlwh = new Vector();
        $tno = 1;
        if ($qd->name) {
            $condname = "CONCAT_WS(' ', person.givenname, person.prefix_name, person.familyname) LIKE '%{$qd->name}%'";
        
            $csqlid->push("(SELECT wiagid FROM person WHERE {$condname}".
                          " UNION SELECT wiagid from familynamevariant WHERE familyname LIKE '%{$qd->name}%'".
                          " UNION SELECT wiagid from givennamevariant WHERE givenname LIKE '%{$qd->name}%') as t{$tno}");
            $tno += 1;
        }

        if ($qd->place) {
            $csqlid->push("(SELECT wiagid_person as wiagid FROM office".
                       " WHERE office.diocese like '%{$qd->place}%') as t{$tno}");
            if ($tno > 1) $csqlwh->push("t1.wiagid = t{$tno}.wiagid");
            $tno += 1;
        }

        if ($qd->facetPlaces) {
            $vp = new Vector($qd->facetPlaces);
            $set_of_dioceses = $vp->map(function ($pl) {return "'{$pl}'";})->join(', ');
            
            $csqlid->push("(SELECT wiagid_person as wiagid FROM office".
                          " WHERE office.diocese IN ({$set_of_dioceses})) AS t{$tno}");
            if ($tno > 1) $csqlwh->push("t1.wiagid = t{$tno}.wiagid");
            $tno += 1;
        }

        // TODO create table with upper and lower time boundary.
        if ($qd->year) {
            $myear = self::MARGINYEAR;
            $csqlid->push("(SELECT wiagid FROM person".
                          " WHERE ABS(person.date_death - {$qd->year}) < {$myear}) as t{$tno}");
            if ($tno > 1) $csqlwh->push("t1.wiagid = t{$tno}.wiagid");
            $tno += 1;
        }

        if ($qd->someid) {
            $condsomeid = "'{$qd->someid}' IN (person.gsid, person.gndid, person.viafid, person.wiagid)";
            $csqlid->push("(SELECT wiagid FROM person".
                       " WHERE {$condsomeid}) as t{$tno}");
            if ($tno > 1) $csqlwh->push("t1.wiagid = t{$tno}.wiagid");
            $tno += 1;
        }
        
        $sqlwhere = $tno > 2 ? " WHERE ".$csqlwh->join(' AND ') : "";
        $sql = "(SELECT DISTINCT(t1.wiagid) as wiagid FROM ".$csqlid->join(', ').$sqlwhere.") AS twiagid";
        
        return $sql;
        
    }
    
    public function countByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "SELECT COUNT(twiagid.wiagid) AS count FROM ".
             $this->buildWiagidSet($querydata);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findPlacesByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "SELECT DISTINCT(diocese) FROM office, ".
             $this->buildWiagidSet($querydata).
             " WHERE office.wiagid_person = twiagid.wiagid AND diocese <> ''";
        
        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findPlacesAndNByQueryObject(BishopQueryFormModel $bishopquery) {
        $conn = $this->getEntityManager()->getConnection();

        // ## TODO COUNT ...
        if (is_null($bishopquery->place)) {
            $sql = "SELECT DISTINCT(office.diocese)".
                 $this->buildWhere($bishopquery).
                 " AND office.diocese <> ''".                 
                 " AND person.wiagid = office.wiagid_person".
                 " GROUP BY office.diocese";
        } else {
            $sql = "SELECT DISTINCT(office.diocese)".
                 $this->buildWhere($bishopquery).
                 " GROUP BY office.diocese";            
        }

        dd($sql);

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
        return $stmt->fetchAll();
    }

    
}
