<?php

namespace App\Repository;

use App\Entity\CnNamelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnNamelookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnNamelookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnNamelookup[]    findAll()
 * @method CnNamelookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnNamelookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnNamelookup::class);
    }

    // /**
    //  * @return CnNamelookup[] Returns an array of CnNamelookup objects
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
    public function findOneBySomeField($value): ?CnNamelookup
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function suggestName($name, $limit = 40): array {
        $qb = $this->createQueryBuilder('cl')
                   ->select("DISTINCT CASE WHEN cl.prefixName <> '' AND cl.familyname <> ''".
                            " THEN CONCAT(cl.givenname, ' ', cl.prefixName, ' ', cl.familyname)".
                            " WHEN cl.familyname <> ''".
                            " THEN CONCAT(cl.givenname, ' ', cl.familyname)".
                            " ELSE cl.givenname END".
                            " AS suggestion")
                   ->andWhere("CONCAT(cl.givenname, ' ', cl.prefixName, ' ', cl.familyname) LIKE :qname".
                              " OR CONCAT(cl.givenname, ' ', cl.familyname)LIKE :qname".
                              " OR cl.givenname LIKE :qname".
                              " OR cl.familyname LIKE :qname")
                   ->setParameter('qname', '%'.$name.'%')
                   ->setMaxResults($limit);

        $suggestions = $qb->getQuery()->getResult();

        return $suggestions;
    }

}
