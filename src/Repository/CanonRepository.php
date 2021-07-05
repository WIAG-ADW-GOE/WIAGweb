<?php

namespace App\Repository;

use App\Entity\Canon;
use App\Entity\CnOffice;
use App\Entity\Monastery;
use App\Form\Model\CanonEditSearchFormModel;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method Canon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Canon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Canon[]    findAll()
 * @method Canon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CanonRepository extends ServiceEntityRepository {
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 1;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Canon::class);
    }

    // /**
    //  * @return Canon[] Returns an array of Canon objects
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
    public function findOneBySomeField($value): ?Canon
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findOneWithOffices($id) {
        // fetch all data related to this canon
        // sorting of offices is specified by an annotation to CanonGS.offices
        $query = $this->createQueryBuilder('canon')
                      ->leftJoin('canon.offices', 'oc')
                      ->addSelect('oc')
                      ->andWhere('canon.id = :id')
                      ->setParameter('id', $id)
                      ->getQuery();

        $canon = $query->getOneOrNullResult();
        return $canon;
    }

     public function findOfficeNames(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->select('DISTINCT oc.officeName, COUNT(DISTINCT(canon.id)) as n')
                   ->join('canon.offices', 'oc');

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('oc.officeName');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * return list of monasteries, where persons have an office;
     * used for the facet of places
     */
    public function findOfficePlaces(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->select('DISTINCT mfacet.wiagid, mfacet.monastery_name, COUNT(DISTINCT(canon.id)) as n')
                   ->join('canon.offices', 'oc')
                   ->join('oc.monastery', 'mfacet')
                   ->andWhere("mfacet.wiagid IN (:domstifte)")
                   ->setParameter('domstifte', Monastery::IDS_DOMSTIFTE);

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('mfacet.monastery_name');

        $query = $qb->getQuery();
        $result = $query->getResult();
        $prefix = "Domstift";
        foreach ($result as $key => $value) {
            $result[$key]['monastery_name'] = Monastery::trimDomstift($result[$key]['monastery_name']);
        }
        return $result;
    }

    /**
     * return list of places, where persons have an office;
     * used for the facet of locations
     */
    public function findOfficeLocations(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->join('canon.offices', 'lfacet')
                   ->select('DISTINCT lfacet.location, lfacet.location, COUNT(DISTINCT(canon.id)) as n')
                   ->andWhere('canon.isready = 1')
                   ->andWhere('lfacet.location IS NOT NULL');


        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('lfacet.location');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function countByQueryObject(CanonEditSearchFormModel $formmodel) {
        // if($formmodel->isEmpty()) return 0;
        $qb = $this->createQueryBuilder('c')
                   ->select('COUNT(DISTINCT c.id)');

        $this->addQueryConditions($qb, $formmodel);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findByQueryObject(CanonEditSearchFormModel $formmodel, $limit = 0, $offset = 0) {

        // join with tables that are needed for sorting anyway
        $qb = $this->createQueryBuilder('c');

        $this->addQueryConditions($qb, $formmodel);


        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());
        // $this->addSortParameter($qb, $formmodel);

        $query = $qb->getQuery();
        // dd($query->getResult());
        $persons = new Paginator($query, true);

        return $persons;
    }

    private function addQueryConditions(QueryBuilder $qb, CanonEditSearchFormModel $formmodel): QueryBuilder {

        // conditions are independent from each other
        // e.g. search for a 'Kanoniker' who had also an office in 'Mainz' says not that the
        // person was 'Kononiker' in 'Mainz';

        # identifier
        if($formmodel->someid) {
            # dump($formmodel->someid);

            $qb->andWhere('c.id like :someid'.
                          ' OR c.gsnId like :someid'.
                          ' OR c.viafId like :someid'.
                          ' OR c.gndId like :someid'.
                          ' OR c.wiagEpiscId like :someid')
               ->setParameter('someid', '%'.$formmodel->someid.'%');
        }

        # year
        if($formmodel->year) {
            $qb->andWhere('c.dateHistFirst - :mgnyear < :qyear AND :qyear < c.dateHistLast + :mgnyear')
               ->setParameter(':mgnyear', self::MARGINYEAR)
               ->setParameter(':qyear', $formmodel->year);
        }

        # monastery
        if($formmodel->monastery) {
            $qb->join('c.offices', 'olt_monastery')
               ->join('olt_monastery.monastery', 'monastery')
               ->join('monastery.domstift', 'query_domstift')
               ->andWhere('monastery.monastery_name LIKE :monastery')
               ->setParameter('monastery', '%'.$formmodel->monastery.'%');
        }

        # office title
        if($formmodel->office) {
            $qb->join('c.offices', 'olt_office')
               ->andWhere('olt_office.officeName LIKE :office')
               ->setParameter('office', '%'.$formmodel->office.'%');
        }

        # office place
        if($formmodel->place) {
            $qb->join('c.offices', 'olt_place')
               ->andWhere('olt_place.location_show LIKE :place OR olt_place.archdeacon_territory LIKE :place')
               ->setParameter('place', '%'.$formmodel->place.'%');
        }

        # names
        if($formmodel->name) {
            $qname = $formmodel->name;
            $cname = explode(' ', $qname);
            if (count($cname) != 2) {
                $qb->andWhere("CONCAT(c.givenname, ' ', c.prefixName, ' ', c.familyname) LIKE :qname".
                              " OR CONCAT(c.givenname, ' ', c.familyname)LIKE :qname".
                              " OR c.givenname LIKE :qname".
                              " OR c.familyname LIKE :qname")
                   ->setParameter('qname', '%'.$qname.'%');
            } else {
                $namestart = $cname[0];
                $nameend = $cname[1];
                $qb->andWhere("c.givenname LIKE :qname OR c.familyname LIKE :qname".
                              " OR CONCAT(c.givenname, ' ', c.familyname) LIKE :qname".
                              " OR CONCAT(c.givenname, ' ', c.prefixName, ' ', c.familyname) LIKE :qname".
                              " OR (c.givenname LIKE :namestart AND c.familyname LIKE :nameend)")
                   ->setParameter('qname', '%'.$qname.'%')
                   ->setParameter('namestart', '%'.$namestart.'%')
                   ->setParameter('nameend', '%'.$nameend.'%');
            }
        }

        # filter by status
        if($formmodel->filterStatus) {
            $qb->andWhere("c.status in (:filter)")
               ->setParameter('filter', $formmodel->filterStatus);
        }

        // for each individual person sort offices by start date in the template
        return $qb;
    }

    public function findStatus() {
        $qb = $this->createQueryBuilder('c')
                   ->select('DISTINCT c.status')
                   ->andWhere('c.status IS NOT NULL');

        return $qb->getQuery()->getResult();
    }

    public function findMerged($id) {
        $qb = $this->createQueryBuilder('c')
                   ->select('DISTINCT c')
                   ->andWhere('c.mergedInto = :id')
                   ->andWhere('c.status = :merged')
                   ->setParameter('id', $id)
                   ->setParameter('merged', 'merged');
        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * AJAX callback
     * suggest name for the edit search form
     */
    public function suggestName($name, $limit = 40): array {
        $select = "DISTINCT CASE WHEN cl.prefixName <> '' AND cl.familyname <> ''".
                " THEN CONCAT(cl.givenname, ' ', cl.prefixName, ' ', cl.familyname)".
                " WHEN cl.familyname <> ''".
                " THEN CONCAT(cl.givenname, ' ', cl.familyname)".
                " ELSE cl.givenname END".
                " AS suggestion";
        $qb = $this->createQueryBuilder('cl');
        // split `name` in order to make the search a bit more flexible
        $cname = explode(' ', $name);
        if (count($cname) != 2) {
            $qb->select($select)
               ->andWhere("cl.givenname LIKE :qname OR cl.familyname LIKE :qname".
                          " OR CONCAT(cl.givenname, ' ', cl.familyname) LIKE :qname".
                          " OR CONCAT(cl.givenname, ' ', cl.prefixName, ' ', cl.familyname) LIKE :qname")
               ->setParameter('qname', '%'.$name.'%')
               ->setMaxResults($limit);
        } else {
            $namestart = $cname[0];
            $nameend = $cname[1];
            $qb->select($select)
               ->andWhere("cl.givenname LIKE :qname OR cl.familyname LIKE :qname".
                          " OR CONCAT(cl.givenname, ' ', cl.familyname) LIKE :qname".
                          " OR CONCAT(cl.givenname, ' ', cl.prefixName, ' ', cl.familyname) LIKE :qname".
                          " OR (cl.givenname LIKE :namestart AND cl.familyname LIKE :nameend)")
               ->setParameter('qname', '%'.$name.'%')
               ->setParameter('namestart', '%'.$namestart.'%')
               ->setParameter('nameend', '%'.$nameend.'%')
               ->setMaxResults($limit);
        }

        $suggestions = $qb->getQuery()->getResult();

        return $suggestions;
    }

    /* AJAX callback */
    public function suggestMerged($input, $limit = 200): array {
        // it is not neccessary that the merge destination is online
        $qb = $this->createQueryBuilder('c')
                   ->select('DISTINCT c.id AS suggestion')
                   ->andWhere('c.id LIKE :input')
                   ->setParameter('input', '%'.$input.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();
    }


}
