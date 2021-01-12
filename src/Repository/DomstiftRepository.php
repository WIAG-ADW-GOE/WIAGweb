<?php

namespace App\Repository;

use App\Entity\Domstift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Domstift|null find($id, $lockMode = null, $lockVersion = null)
 * @method Domstift|null findOneBy(array $criteria, array $orderBy = null)
 * @method Domstift[]    findAll()
 * @method Domstift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DomstiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domstift::class);
    }

    // /**
    //  * @return Domstift[] Returns an array of Domstift objects
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
    public function findOneBySomeField($value): ?Domstift
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /* AJAX callback */
    public function suggestDomstift($domstift, $limit) {
        $qb = $this->createQueryBuilder('dt')
                   ->select('DISTINCT dt.name AS suggestion')
                   ->andWhere('dt.name LIKE :domstift')
                   ->setParameter('domstift', '%'.$domstift.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        return $query->getResult();

    }

    public function findChoicelist() {
        $objectlist = $this->findAll();

        $choicelist = array();
        foreach($objectlist as $obj) {
            $choicelist[$obj->getName()] = $obj->getGsId();
        }

        return $choicelist;
    }

}
