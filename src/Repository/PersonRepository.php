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

        if (is_null($querydata->place) and !$querydata->facetPlaces) {
            $sqltables = "person";
        } else {
            $sqltables = "person, office";
        }

        $condclause = "";
        
        if ($querydata->year) {
            // mysql is quite robust
            $thyear = self::MARGINYEAR;
            $condclause = " ABS(person.date_death - {$querydata->year}) < {$thyear}";
        }
        
        if ($querydata->someid) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." '{$querydata->someid}' IN (person.gsid, person.gndid, person.viafid)";
        }
        
        if ($querydata->name) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." CONCAT_WS(' ', person.givenname, person.prefix_name, person.familyname)".
                        " LIKE '%{$querydata->name}%'";
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

        return " FROM ".$sqltables." WHERE". $condclause;
        
    }

    public function countByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "SELECT COUNT(DISTINCT(person.wiagid)) as count".
             $this->buildWhere($querydata);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findPlacesByQueryObject(BishopQueryFormModel $bishopquery) {
        $conn = $this->getEntityManager()->getConnection();

        if (!$bishopquery->place) {
            $sql = "SELECT DISTINCT(office.diocese)".
                 $this->buildWhere($bishopquery).
                 " AND person.wiagid = office.wiagid_person";
        } else {
            $sql = "SELECT DISTINCT(office.diocese)".
                 $this->buildWhere($bishopquery);        
        }

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

        /* Doctrine Querybuilder may allow to combine conditions in a more flexible way. */

        if (is_null($querydata->place)) {
            $sqltables = "person";
        } else {
            $sqltables = "person, office";
        }

        $offset = ($page - 1) * $limit;

        $sql = "SELECT DISTINCT(person.wiagid), person.*".
             $this->buildWhere($querydata).
             " LIMIT {$limit} OFFSET {$offset}";


        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    
}
