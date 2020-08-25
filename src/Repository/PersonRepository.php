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
    const THYEAR = 50;
    
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

    public function suggestName($name, $limit = 1000): array {
        $conn = $this->getEntityManager()->getConnection();

        //         ORDER BY p.familyname ASC 
        
        $sql = "
        SELECT DISTINCT(CONCAT_WS(' ', p.givenname, p.prefix, p.familyname)) as suggestion FROM person p
        WHERE CONCAT_WS(' ', p.givenname, p.prefix, p.familyname) LIKE :name
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
        
        $sql = "
        SELECT * FROM person p
        WHERE p.familyname LIKE :name
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

    private function buildWhere(BishopQueryFormModel $querydata): string {
        $condclause = "";
        
        if ($querydata->year) {
            // mysql is quit robust
            $thyear = self::THYEAR;
            $condclause = " ABS(date_death - {$querydata->year}) < {$thyear}";
        }
        
        if ($querydata->someid) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." '{$querydata->someid}' IN (gsid, gndid, viafid)";
        }
        
        if ($querydata->name) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." CONCAT_WS(' ', givenname, prefix, familyname) LIKE '%{$querydata->name}%'";
        }

        if ($querydata->place) {
            $condclause = $condclause ? $condclause." AND" : "";
            $condclause = $condclause." person.wiagid = office.wiagid_person AND ".
                        "diocese like '%{$querydata->place}%'";
        }

        return $condclause;
        
    }

    public function countByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();

        if (is_null($querydata->place)) {
            $sqltables = "person";
        } else {
            $sqltables = "person, office";
        }

        $sql = "SELECT COUNT(DISTINCT(person.wiagid)) as count FROM ${sqltables} WHERE".
             $this->buildWhere($querydata);

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

        $sql = "SELECT DISTINCT(person.wiagid), person.* FROM ${sqltables} WHERE".
             $this->buildWhere($querydata).
             " LIMIT {$limit} OFFSET {$offset}";


        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    


    
}
