<?php

namespace App\Repository;

use App\Entity\Canon;
use App\Entity\CnOffice;
use App\Form\Model\CanonFormModel;

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
class CanonRepository extends ServiceEntityRepository
{
    // Allow deviations in the query parameter `year`.
    const MARGINYEAR = 50;
    
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

    public function countByQueryObject(CanonFormModel $formmodel) {
        if($formmodel->isEmpty()) return 0;

        $qb = $this->createQueryBuilder('canon')
                   ->select('COUNT(DISTINCT canon.id)')
                   ->andWhere('canon.isready = 1');

        $this->addQueryConditions($qb, $formmodel);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findWithOffices(CanonFormModel $formmodel, $limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->leftJoin('canon.offices', 'oc')
                   ->addSelect('oc')
                   ->leftJoin('oc.numdate', 'ocdatecmp')
                   ->addSelect('ocdatecmp');

        $this->addQueryConditions($qb, $formmodel);


        if($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        // dump($qb->getDQL());
        # TODO 
        // $this->addSortParameter($qb, $formmodel);

        $query = $qb->getQuery();
        // dd($query->getResult());
        $persons = new Paginator($query, true);



        return $persons;
    }


    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        # identifier
        if($formmodel->someid) {
            $qb->andWhere(":someid = canon.id".
                          " OR :someid = canon.gsnId".
                          " OR :someid = canon.viafId".
                          " OR :someid = canon.wikidataId".
                          " OR :someid = canon.gndId")
               ->setParameter(':someid', $formmodel->someid);
        }

        # year
        if($formmodel->year) {
            $erajoined = true;
            $qb->join('canon.era', 'era')
                ->andWhere('era.eraStart - :mgnyear < :qyear AND :qyear < era.eraEnd + :mgnyear')
                ->setParameter(':mgnyear', self::MARGINYEAR)
                ->setParameter(':qyear', $formmodel->year);
        }

        # office title
        if($formmodel->office) {
            // we have to join office a second time to filter at the level of persons
            $qb->join('canon.offices', 'octitle')
                ->andWhere('octitle.officeName LIKE :office')
                ->setParameter('office', '%'.$formmodel->office.'%');
        }

        # office diocese
        if($formmodel->place) {
            // we have to join office a second time to filter at the level of persons
            $sort = 'yearatplace';
            $qb->join('canon.officeSortkeys', 'ocselectandsort')
                ->andWhere('ocselectandsort.diocese LIKE :place')
                ->setParameter('place', '%'.$formmodel->place.'%');
        }
        # names
        if($formmodel->name) {
            $qb->join('canon.namelookup', 'nlt')
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefixName, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->setParameter('qname', '%'.$formmodel->name.'%');
        }


        $this->addFacets($formmodel, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    public function addSortParameter($qb, $formmodel) {

        $sort = 'name';
        if($formmodel->year || $formmodel->office) $sort = 'year';
        if($formmodel->place) $sort = 'yearatplace';
        if($formmodel->name) $sort = 'name';

        /**
         * a reliable order is required, therefore canon.givenname shows up
         * in each sort clause
         */

        switch($sort) {
        case 'year':
            $qb->leftJoin('canon.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, canon.givenname');
            break;
        case 'yearatplace':
            $qb->orderBy('ocselectandsort.sortkey, canon.givenname');
            break;
        case 'name':
            // $qb->orderBy('canon.familyname, canon.givenname, oc.diocese');
            $qb->leftJoin('canon.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, canon.givenname');
            break;
        }

        return $qb;

    }

    public function addMonasteryLocation(Canon $person) {
        $em = $this->getEntityManager();
        $officeRepository = $em->getRepository(CnOffice::class);
        foreach($person->getOffices() as $oc) {
            $officeRepository->setMonasteryLocation($oc);
        }
    }

    public function findOneWithOffices($id) {
        // fetch all data related to this canon
        $query = $this->createQueryBuilder('canon')
                      ->andWhere('canon.id = :id')
                      ->setParameter('id', $id)
                      ->leftJoin('canon.offices', 'oc')
                      ->leftJoin('oc.numdate', 'ocdate')
                      ->orderBy('ocdate.dateStart', 'ASC')
                      ->leftJoin('oc.monastery', 'monastery')
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

    public function findOfficePlaces(CanonFormModel $canonquery) {
        $qb = $this->createQueryBuilder('canon')
                   ->andWhere('canon.isready = 1')
                   ->select('DISTINCT oc.diocese, COUNT(DISTINCT(canon.id)) as n')
                   ->join('canon.offices', 'oc')
                   ->andWhere("oc.diocese <> ''");

        $this->addQueryConditions($qb, $canonquery);

        $qb->groupBy('oc.diocese');

        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function addFacets($querydata, $qb) {
        if($querydata->facetPlaces) {
            $facetdioceses = array();
            foreach($querydata->facetPlaces as $d) {
                $facetdioceses[] = $d->name;
            }
            $qb->join('canon.offices', 'ocfctp')
               ->andWhere("ocfctp.diocese IN (:facetdioceses)")
               ->setParameter('facetdioceses', $facetdioceses);
        }
        if($querydata->facetOffices) {
            $facetoffices = array();
            foreach($querydata->facetOffices as $d) {
                $facetoffices[] = $d->name;
            }
            $qb->join('canon.offices', 'ocfctoc')
               ->andWhere("ocfctoc.officeName IN (:facetoffices)")
               ->setParameter('facetoffices', $facetoffices);
        }

        return $qb;
    }

    
}