<?php

namespace App\Repository;

use App\Entity\Canon;
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
                   ->select('COUNT(DISTINCT canon.id)');

        # TODO
        # $this->addQueryConditions($qb, $formmodel);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findWithOffices(CanonFormModel $formmodel, $limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('canon')
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

        $this->addSortParameter($qb, $formmodel);

        $query = $qb->getQuery();
        // dd($query->getResult());

        $persons = new Paginator($query, true);

        // $persons = $query->getResult();

        return $persons;
    }


    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        # identifier
        if($formmodel->someid) {
            $qb->andWhere(":someid = person.wiagid".
                          " OR :someid = person.gsid".
                          " OR :someid = person.viafid".
                          " OR :someid = person.wikidataid".
                          " OR :someid = person.gndid")
               ->setParameter(':someid', $formmodel->someid);
        }

        # year
        if($formmodel->year) {
            $erajoined = true;
            $qb->join('canon.era', 'era')
                ->andWhere('era.era_start - :mgnyear < :qyear AND :qyear < era.era_end + :mgnyear')
                ->setParameter(':mgnyear', self::MARGINYEAR)
                ->setParameter(':qyear', $formmodel->year);
        }

        # office title
        if($formmodel->office) {
            // we have to join office a second time to filter at the level of persons
            $qb->join('canon.offices', 'octitle')
                ->andWhere('octitle.office_name LIKE :office')
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
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefix_name, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->setParameter('qname', '%'.$formmodel->name.'%');
        }

        # TODO
        # $this->addFacets($formmodel, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    public function addSortParameter($qb, $formmodel) {

        $sort = 'name';
        if($formmodel->year || $formmodel->office) $sort = 'year';
        if($formmodel->place) $sort = 'yearatplace';
        if($formmodel->name) $sort = 'name';

        /**
         * a reliable order is required, therefore person.givenname shows up
         * in each sort clause
         */

        switch($sort) {
        case 'year':
            // if(!$formmodel->year) {
            //     $qb->join('canon.era', 'era');
            // }
            // $qb->orderBy('era.era_start, person.givenname');
            $qb->leftJoin('canon.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, person.givenname');
            break;
        case 'yearatplace':
            // $qb->join('ocplace.numdate', 'ocplacedate')
            //    ->orderBy('ocplacedate.date_start, person.givenname', 'ASC');
            $qb->orderBy('ocselectandsort.sortkey, person.givenname');
            break;
        case 'name':
            // $qb->orderBy('canon.familyname, person.givenname, oc.diocese');
            $qb->leftJoin('canon.officeSortkeys', 'ocsortkey')
               ->addSelect('ocsortkey')
               ->andWhere('ocsortkey.diocese = :diocese')
               ->setParameter('diocese', 'all')
               ->orderBy('ocsortkey.sortkey, person.givenname');
            break;
        }

        return $qb;

    }

    
}
