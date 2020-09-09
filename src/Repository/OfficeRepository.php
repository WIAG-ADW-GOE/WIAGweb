<?php

namespace App\Repository;

use App\Entity\Office;
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

    public function findByIDPerson($id_person): array {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
        SELECT * FROM office oe
        WHERE oe.wiagid_person = :id_person
        ORDER BY oe.date_end DESC
        ";
        $stmt = $conn->prepare($sql);
        // is it possible to reuse prepared statements?
        $stmt->execute(['id_person' => $id_person]);


        // returns an array of arrays (i.e. a raw data set)
        return $stmt->fetchAll();
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


}
