<?php

namespace App\Repository;

use App\Entity\Diocese;
use App\Entity\Place;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Diocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method Diocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method Diocese[]    findAll()
 * @method Diocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DioceseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diocese::class);
    }

    // /**
    //  * @return Diocese[] Returns an array of Diocese objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Diocese
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findWithBishopricSeat($idorname) {
        $id = DIOCESE::wiagidLongToId($idorname);
        $diocese = null;
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->join('diocese.bishopricseatobj', 'placeobj');

        if(preg_match('/[0-9]/', $id) > 0) {
            $qb->andWhere('diocese.id_diocese = :id')
               ->setParameter('id', $id);
        } else {
            $qb->andWhere('diocese.diocese = :id')
               ->setParameter('id', $id);
        }

        $query = $qb->getQuery();
        $diocese = $query->getOneOrNullResult();

        if($diocese === null)
            return null;


        return $diocese;
    }

    public function findAllWithBishopricSeat($page, $limit) {
        $offset = ($page - 1) * $limit;
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->join('diocese.bishopricseatobj', 'placeobj')
                   ->setFirstResult($offset)
                   ->setMaxResults($limit);
        $query = $qb->getQuery();
        $dioceses = $query->getResult();
        return $dioceses;
    }

}
