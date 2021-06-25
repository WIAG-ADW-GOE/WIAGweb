<?php

namespace App\Repository;

use App\Entity\CnEra;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnEra|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnEra|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnEra[]    findAll()
 * @method CnEra[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnEraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnEra::class);
    }

    // /**
    //  * @return CnEra[] Returns an array of CnEra objects
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
    public function findOneBySomeField($value): ?CnEra
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function deleteByIdOnline($id_online) {
        $this->createQueryBuilder('e')
             ->delete()
             ->andWhere('e.idOnline = :id_online')
             ->setParameter('id_online', $id_online)
             ->getQuery()
             ->getResult();
    }


}
