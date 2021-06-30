<?php

namespace App\Repository;

use App\Entity\CnOfficelookup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOfficelookup|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOfficelookup|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOfficelookup[]    findAll()
 * @method CnOfficelookup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficelookupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOfficelookup::class);
    }

    // /**
    //  * @return CnOfficelookup[] Returns an array of CnOfficelookup objects
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
    public function findOneBySomeField($value): ?CnOfficelookup
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /* AJAX callback */
    public function suggestMonastery($monastery, $limit = 100): array {
        $qb = $this->createQueryBuilder('olt')
                   ->select('DISTINCT monastery.monastery_name AS suggestion')
                   ->join('olt.monastery', 'monastery')
                   ->andWhere('monastery.monastery_name LIKE :monastery')
                   ->setParameter('monastery', '%'.$monastery.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();

    }


    /* AJAX callback */
    public function suggestPlace($place, $limit = 100): array {
        $qb = $this->createQueryBuilder('olt')
                   ->select('DISTINCT olt.locationName AS suggestion')
                   ->andWhere('olt.locationName LIKE :place')
                   ->setParameter('place', '%'.$place.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        $suggestions = $query->getResult();

        $nloc = count($suggestions);
        if ($nloc < $limit) {
            $limit_at = $limit - $nloc;
            $qb_at = $this->createQueryBuilder('olt')
                          ->select('DISTINCT olt.archdeaconTerritory AS suggestion')
                          ->andWhere('olt.archdeaconTerritory LIKE :place')
                          ->setParameter('place', '%'.$place.'%')
                          ->setMaxResults($limit_at);
            $query_at = $qb_at->getQuery();
            $suggestions_at = $query_at->getResult();
            $suggestions = array_merge($suggestions, $suggestions_at);
        }

        # dd($suggestions);

        return $suggestions;
    }

    // obsolete 2021-06-28
    // public function deleteByIdOnline($id_online) {
    //     $this->createQueryBuilder('olt')
    //          ->delete()
    //          ->andWhere('olt.idOnline = :id_online')
    //          ->setParameter('id_online', $id_online)
    //          ->getQuery()
    //          ->getResult();
    // }

    public function deleteByIds($ids) {
        $this->createQueryBuilder('olt')
             ->delete()
             ->andWhere('olt.id in (:ids)')
             ->setParameter('ids', $ids)
             ->getQuery()
             ->getResult();
    }

    public function deleteByIdOnline($id_online) {
        $qb = $this->createQueryBuilder('olt')
                   ->delete()
                   ->andWhere('olt.idOnline = :id_online')
                   ->setParameter('id_online', $id_online);
        $query = $qb->getQuery();
        $query->getResult();
    }



    /**
     * find domstift for cn_online
     */
    public function findFirstDomstift($id_online) {
        $qb = $this->createQueryBuilder('o')
                   ->select('d.name, min(o.numdateStart) as numdate_start')
                   ->join('\App\Entity\Domstift', 'd', 'WITH', 'o.idMonastery = d.gs_id')
                   ->andWhere('o.idOnline = :id_online')
                   ->setParameter('id_online', $id_online);
        $query = $qb->getQuery();

        return $query->getResult();

    }


    /* AJAX callback */
    public function suggestOffice($office, $limit = 100): array {
        $qb = $this->createQueryBuilder('olt')
                   ->select('DISTINCT olt.officeName AS suggestion')
                   ->andWhere('olt.officeName LIKE :title')
                   ->setParameter('title', '%'.$office.'%')
                   ->setMaxResults($limit);

        $query = $qb->getQuery();

        return $query->getResult();

    }

}
