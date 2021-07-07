<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\Person;
use App\Form\Model\BishopQueryFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Office|null find($id, $lockMode = null, $lockVersion = null)
 * @method Office|null findOneBy(array $criteria, array $orderBy = null)
 * @method Office[]    findAll()
 * @method Office[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfficeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Office::class);
    }

    // /**
    //  * @return Office[] Returns an array of Office objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Office
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByIDPerson($wiagid): array {

        $query = $this->getEntityManager()
                   ->createQuery('SELECT o FROM App\Entity\Office o'.
                                 ' JOIN App\Entity\Officedate od '.
                                 ' WHERE o.wiagid_person = '.$wiagid.
                                 ' AND od.wiagid_office = o.wiagid '.
                                 ' ORDER BY od.date_start, od.date_end');
         return $query->getResult();

    }

    /* AJAX callback */
    public function suggestPlace($place, $limit = 200): array {
        $qb = $this->createQueryBuilder('oc')
                   ->select('DISTINCT oc.diocese AS suggestion')
                   ->andWhere('oc.diocese LIKE :place')
                   ->setParameter('place', '%'.$place.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();

    }

    /* AJAX callback */
    public function suggestOffice($office, $limit = 200): array {
        $qb = $this->createQueryBuilder('oc')
                   ->select('DISTINCT oc.office_name AS suggestion')
                   ->andWhere('oc.office_name LIKE :title')
                   ->setParameter('title', '%'.$office.'%')
                   ->setMaxResults($limit);

        $query = $qb->getQuery();

        return $query->getResult();

    }

    public function findPersonIdsByTitle(BishopQueryFormModel $bishopquery) {
        $qb = $this->createQueryBuilder('oc')
                   ->select('wiagid_person')
                   ->andWhere('oc.office_name LIKE :title')
                   ->setParameter('title', '%'.$bishopquery->office.'%');

            $query = $qb->getQuery();
            $wiagids = $query->getResult();

            return $wiagids;
    }

    public function setMonasteryLocation(Office $oc) {

        if($oc->getIdMonastery()) {

            // DQL
            $em = $this->getEntityManager();

            // $query = $em->createQuery("SELECT loc.location_name, loc.location_begin_tpq, loc.location_end_tpq ".
            //                           "FROM App\Entity\Office oc ".
            //                           "LEFT JOIN App\Entity\MonasteryLocation loc ".
            //                           "WITH loc.wiagid_monastery = oc.id_monastery ".
            //                           "WHERE oc.wiagid = :ocid ".
            //                           "AND loc.location_name IS NOT NULL")
            //             ->setParameter('ocid', $oc->getWiagid());

            $query = $em->createQuery("SELECT loc.locationName, loc.location_begin_tpq, loc.location_end_tpq ".
                                      "FROM App\Entity\MonasteryLocation loc ".
                                      "WHERE loc.wiagidMonastery = :idMonastery ".
                                      "AND loc.locationName IS NOT NULL")
                        ->setParameter('idMonastery', $oc->getIdMonastery());


            $qrplaces = $query->getResult();

            $qrplaces_count = count($qrplaces);


            $places = "";
            if($qrplaces_count == 1) {
                $places = $qrplaces[0]['locationName'];
            } elseif($qrplaces_count > 1) {
                $locations_s = $this->checkMonasteryLocationDates($qrplaces, $oc);
                $places = $this->selectandjoin($locations_s, 'locationName');
            } else {
                $qrplaces = $this->findMonasteryLocationByPlaceId($oc);
                $locations_s = $this->checkMonasteryLocationDates($qrplaces, $oc);
                $places = $this->selectandjoin($locations_s, 'place_name');
            }

            $oc->setMonasterylocationstr($places);
        }
    }

    public function checkMonasteryLocationDates($locations, Office $oc) {
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

    public function findMonasteryLocationByPlaceId(Office $oc) {
        $dql = "SELECT place.place_name as place_name, ".
             "loc.location_begin_tpq, loc.location_end_tpq ".
             "FROM App\Entity\Office oc ".
             "INNER JOIN App\Entity\MonasteryLocation loc ".
             "WITH loc.wiagidMonastery = oc.id_monastery ".
             "INNER JOIN App\Entity\Place place ".
             "WITH place.idPlaces = loc.place_id ".
             "WHERE oc.wiagid = :ocid ";

        $em = $this->getEntityManager();
        $query = $em->createQuery($dql)
                    ->setParameter('ocid', $oc->getWiagid());

        $qrplaces = $query->getResult();

        return $qrplaces;
    }

}
