<?php

namespace App\Repository;

use App\Entity\CnOffice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CnOffice|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOffice|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOffice[]    findAll()
 * @method CnOffice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOfficeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CnOffice::class);
    }

    // /**
    //  * @return CnOffice[] Returns an array of CnOffice objects
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
    public function findOneBySomeField($value): ?CnOffice
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    // 2021-04-07 obsolete: use CnOffice.location instead
    public function setMonasteryLocation(CnOffice $oc) {

        if($oc->getIdMonastery()) {
            // DQL
            $em = $this->getEntityManager();

            $query = $em->createQuery("SELECT loc.location_name, loc.location_begin_tpq, loc.location_end_tpq ".
                                      "FROM App\Entity\MonasteryLocation loc ".
                                      "WHERE loc.wiagid_monastery = :idMonastery ".
                                      "AND loc.location_name IS NOT NULL")
                        ->setParameter('idMonastery', $oc->getIdMonastery());

            $qrplaces = $query->getResult();

            $qrplaces_count = count($qrplaces);


            $places = "";
            if($qrplaces_count == 1) {
                $places = $qrplaces[0]['location_name'];
            } elseif($qrplaces_count > 1) {
                $locations_s = $this->checkMonasteryLocationDates($qrplaces, $oc);
                $places = $this->selectandjoin($locations_s, 'location_name');
            } else {
                $qrplaces = $this->findMonasteryLocationByPlaceId($oc);
                $locations_s = $this->checkMonasteryLocationDates($qrplaces, $oc);
                $places = $this->selectandjoin($locations_s, 'place_name');
            }

            $oc->setMonasterylocationstr($places);
        }
    }

    // 2021-04-07 obsolete: use CnOffice.location instead
    public function checkMonasteryLocationDates($locations, CnOffice $oc) {
        $locations_s = array();
        foreach($locations as $el) {
            $l_begin = intval($el['location_begin_tpq']);
            $oc_begin = intval($oc->getDateStart());
            $oc_end = intval($oc->getDateEnd());
            if($l_begin && $oc_end && $l_begin > $oc_end)
                continue;
            $l_end = $el['location_end_tpq'];
            if($l_end && $oc_begin && $l_end < $oc_begin)
                continue;
            $locations_s[] = $el;
        }
        return $locations_s;
    }


    public function selectandjoin(array $a, string $field) {
        $as = array();
        foreach($a as $el) {
            $as[] = $el[$field];
        }
        $as = array_unique($as);
        return implode(", ", $as);
    }

    // 2021-04-07 obsolete: use CnOffice.location instead
    public function findMonasteryLocationByPlaceId(CnOffice $oc) {
        // $sql = "SELECT place.place_name as place_name, ".
        //      "loc.location_begin_tpq, loc.location_end_tpq ".
        //      "FROM App\Entity\CnOffice oc ".
        //      "INNER JOIN App\Entity\MonasteryLocation loc ".
        //      "WITH loc.wiagid_monastery = oc.idMonastery ".
        //      "INNER JOIN App\Entity\Place place ".
        //      "WITH place.id_places = loc.place_id ".
        //      "WHERE oc.id = :ocid ";

        $sql = "SELECT place.place_name as place_name, ".
             "loc.location_begin_tpq, loc.location_end_tpq ".
             "FROM App\Entity\MonasteryLocation loc ".
             "INNER JOIN App\Entity\Place place ".
             "WITH place.id_places = loc.place_id ".
             "WHERE loc.wiagid_monastery = :idMonastery ";


        $em = $this->getEntityManager();
        $query = $em->createQuery($sql)
                    ->setParameter('idMonastery', $oc->getIdMonastery());

        $qrplaces = $query->getResult();

        return $qrplaces;
    }

    public function findByIdCanonAndSort($id_canon) {
        $qb = $this->createQueryBuilder('o')
                   ->andWhere('o.idCanon = :idCanon')
                   ->setParameter('idCanon', $id_canon)
            // ->join('o.monastery', 'monastery')
            // ->addOrderBy('monastery.monastery_name', 'ASC')
                   ->addOrderBy('o.location_show', 'ASC')
                   ->addOrderBy('o.idMonastery', 'ASC')
                   ->addOrderBy('o.dateStart', 'ASC');
        $query = $qb->getQuery();

        return $query->getResult();
    }

}
