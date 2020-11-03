<?php

namespace App\Repository;

use App\Entity\Namelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Namelookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method Namelookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method Namelookup[]    findAll()
 * @method Namelookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NamelookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Namelookup::class);
    }

    // /**
    //  * @return Namelookup[] Returns an array of Namelookup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Namelookup
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function suggestName($name, $limit = 40): array {
        $qb = $this->createQueryBuilder('cl')
                   ->select("DISTINCT CASE WHEN cl.prefix_name <> '' AND cl.familyname <> ''".
                            " THEN CONCAT(cl.givenname, ' ', cl.prefix_name, ' ', cl.familyname)".
                            " WHEN cl.familyname <> ''".
                            " THEN CONCAT(cl.givenname, ' ', cl.familyname)".
                            " ELSE cl.givenname END".
                            " AS suggestion")
                   ->andWhere("CONCAT(cl.givenname, ' ', cl.prefix_name, ' ', cl.familyname) LIKE :qname".
                              " OR CONCAT(cl.givenname, ' ', cl.familyname)LIKE :qname".
                              " OR cl.givenname LIKE :qname".
                              " OR cl.familyname LIKE :qname")
                   ->setParameter('qname', '%'.$name.'%')
                   ->setMaxResults($limit);

        $suggestions = $qb->getQuery()->getResult();

        return $suggestions;
    }

}
