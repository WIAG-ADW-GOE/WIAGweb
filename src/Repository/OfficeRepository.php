<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\Person;
use App\Form\Model\BishopQueryFormModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Office|null find($id, $lockMode = null, $lockVersion = null)
 * @method Office|null findOneBy(array $criteria, array $orderBy = null)
 * @method Office[]    findAll()
 * @method Office[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfficeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Office::class);
    }

    // /**
    //  * @return Office[] Returns an array of Office objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Office
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByIDPerson($wiagid): array {

        $qb = $this->getEntityManager()
                   ->createQuery('SELECT o FROM App\Entity\Office o'.
                                 ' JOIN App\Entity\Officedate od '.
                                 ' WHERE o.wiagid_person = '.$wiagid.
                                 ' AND od.wiagid_office = o.wiagid '.
                                 ' ORDER BY od.date_start, od.date_end');
         return $qb->getResult();
        
        // $conn = $this->getEntityManager()->getConnection();        

        // $sql = "
        // SELECT oe.* FROM office oe, officedate od
        // WHERE oe.wiagid_person = :id_person AND oe.wiagid = od.wiagid_office
        // ORDER BY od.date_start ASC
        // ";
        // $stmt = $conn->prepare($sql);
        // // is it possible to reuse prepared statements?
        // $stmt->execute(['id_person' => $id_person]);


        // // returns an array of arrays (i.e. a raw data set)
        // return $stmt->fetchAll();
    }

    public function suggestPlace($place, $limit = 1000): array {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "
        SELECT DISTINCT(diocese) as suggestion FROM office p
        WHERE p.diocese LIKE :place
        LIMIT $limit
        ";
        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute([
            'place' => "%{$place}%",
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    public function suggestOffice($office, $limit = 1000): array {
        $conn = $this->getEntityManager()->getConnection();


        $sql = "
        SELECT DISTINCT(office_name) as suggestion FROM office o
        WHERE o.office_name LIKE :office
        LIMIT $limit
        ";
        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute([
            'office' => "%{$office}%",
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
    }

    /**
     * Find all offices (with frequency) for a given query.
     */
    public function findOfficeNamesByQueryObject(BishopQueryFormModel $querydata) {
        $conn = $this->getEntityManager()->getConnection();

        $pRep = $this->getEntityManager()->getRepository(Person::class);
        
        $sql = "SELECT DISTINCT(office_name), COUNT(DISTINCT(wiagid_person)) as n FROM office, ".
             $pRep->buildWiagidSet($querydata).
             " WHERE office.wiagid_person = twiagid.wiagid AND diocese <> ''".
             " GROUP BY office_name ORDER BY NULL";

        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Find all dioceses (with frequency) for a given query.
     */
    public function findDiocesesByQueryObject(BishopQueryFormModel $querydata) {
        
        $conn = $this->getEntityManager()->getConnection();
        $pRep = $this->getEntityManager()->getRepository(Person::class);

        /** 
         * 'ORDER BY NULL' is the most efficient way to order by the column used in 'GROUP BY' see
         * https://dev.mysql.com/doc/refman/5.7/en/select.html
         */
        if ($querydata->isEmpty()) {
            $sql = "SELECT DISTINCT(diocese), COUNT(DISTINCT(wiagid_person)) as n FROM office ".
                 " WHERE diocese <> ''".
                 " GROUP BY diocese ORDER BY NULL"; 
        } else {
            $sql = "SELECT DISTINCT(diocese), COUNT(DISTINCT(wiagid_person)) as n FROM office, ".
                 $pRep->buildWiagidSet($querydata).
                 " WHERE office.wiagid_person = twiagid.wiagid AND diocese <> ''".
                 " GROUP BY diocese ORDER BY NULL";
        }

        // dd($bishopquery, $sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

}
