<?php

namespace App\Repository;

use App\Entity\CanonGS;
use App\Entity\CnOfficeGS;
use App\Entity\Monastery;
use App\Form\Model\CanonFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method CanonGS|null find($id, $lockMode = null, $lockVersion = null)
 * @method CanonGS|null findOneBy(array $criteria, array $orderBy = null)
 * @method CanonGS[]    findAll()
 * @method CanonGS[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CanonGSRepository extends ServiceEntityRepository {
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CanonGS::class);
    }

    // /**
    //  * @return CanonGS[] Returns an array of CanonGS objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CanonGS
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function findOneWithOffices($id) {
        // fetch all data related to this canon
        $db_id = CanonGS::extractDbId($id);
        $id_param = $db_id ? $db_id : $id;
        $query = $this->createQueryBuilder('canon')
                      ->andWhere('canon.id = :id')
                      ->setParameter('id', $id_param)
                      ->leftJoin('canon.offices', 'oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.dateStart', 'ASC')
                      ->leftJoin('oc.monastery', 'monastery')
                      ->getQuery();

        $canon = $query->getOneOrNullResult();

        return $canon;
    }


}
