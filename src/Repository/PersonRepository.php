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


    public function findByFamilyname($name): array {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
        SELECT * FROM person p
        WHERE p.familyname like :name
        ORDER BY p.familyname ASC
        ";
        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute(['name' => "%{$name}%"]);
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function findByQueryObject(BishopQueryFormModel $querydata, $limit, $page): array {
        $conn = $this->getEntityManager()->getConnection();

        /* TODO split name elements and search for them in 'familyname' and 'givenname'
         * or introduce a field 'name'. The latter makes more sense, because of the selection 
         * hints that we will use.
         */
        /* Doctrine Querybuilder allows to combine conditions in a more flexible way. */
        $condclause = "";
        if (is_null($querydata->place)) {

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
                $condclause = $condclause." familyname like '%{$querydata->name}%' OR ".
                            $condclause." givenname like '%{$querydata->name}%'";
            }       
        }
        
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM person WHERE".
             $condclause.
             " LIMIT {$limit} OFFSET {$offset}";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    


    
}
