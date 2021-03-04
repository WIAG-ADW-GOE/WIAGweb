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
    
    public function setMonasteryLocation(CnOffice $oc) {

        if($oc->getIdMonastery()) {
            // DQL
            $em = $this->getEntityManager();

            $query = $em->createQuery("SELECT loc.location_name, loc.location_begin_tpq, loc.location_end_tpq ".
                                      "FROM App\Entity\CnOffice oc ".
                                      "INNER JOIN App\Entity\MonasteryLocation loc ".
                                      "WITH loc.wiagid_monastery = oc.idMonastery ".
                                      "WHERE oc.id = :ocid ".
                                      "AND loc.location_name IS NOT NULL")
                        ->setParameter('ocid', $oc->getId());

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
        return implode(", ", $as);
    }

    public function findMonasteryLocationByPlaceId(CnOffice $oc) {
        $sql = "SELECT place.place_name as place_name, ".
             "loc.location_begin_tpq, loc.location_end_tpq ".
             "FROM App\Entity\CnOffice oc ".
             "INNER JOIN App\Entity\Monasterylocation loc ".
             "WITH loc.wiagid_monastery = oc.idMonastery ".
             "INNER JOIN App\Entity\Place place ".
             "WITH place.id_places = loc.place_id ".
             "WHERE oc.id = :ocid ";

        $em = $this->getEntityManager();
        $query = $em->createQuery($sql)
                    ->setParameter('ocid', $oc->getId());

        $qrplaces = $query->getResult();

        return $qrplaces;
    }

}
