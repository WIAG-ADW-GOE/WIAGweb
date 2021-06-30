<?php

namespace App\Repository;

use App\Entity\CnReference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnReference|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnReference|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnReference[]    findAll()
 * @method CnReference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnReference::class);
    }

    // /**
    //  * @return CnReference[] Returns an array of CnReference objects
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
    public function findOneBySomeField($value): ?CnReference
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findShorttitleById($id) {
        return $this->createQueryBuilder('c')
                    ->select('c.shorttitle')
                    ->andWhere('c.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();

    }

    public function findIdByShorttitle($st) {
        return $this->createQueryBuilder('c')
                    ->select('c.id')
                    ->andWhere('c.shorttitle = :st')
                    ->setParameter('st', $st)
                    ->getQuery()
                    ->getOneOrNullResult();
    }


    /* AJAX callback */
    public function suggestShorttitle($input, $limit = 200): array {
        $qb = $this->createQueryBuilder('c')
                   ->select('DISTINCT c.shorttitle AS suggestion')
                   ->andWhere(' c.shorttitle LIKE :input')
                   ->setParameter('input', '%'.$input.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();
    }

}
