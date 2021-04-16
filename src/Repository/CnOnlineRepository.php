<?php

namespace App\Repository;

use App\Entity\CnOnline;
use App\Form\Model\CanonFormModel;
use App\Repository\CanonRepository;
use App\Repository\CnOfficeRepository;
use App\Repository\CanonGSRepository;
use App\Repository\CnOfficeGSRepository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;


/**
 * @method CnOnline|null find($id, $lockMode = null, $lockVersion = null)
 * @method CnOnline|null findOneBy(array $criteria, array $orderBy = null)
 * @method CnOnline[]    findAll()
 * @method CnOnline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CnOnlineRepository extends ServiceEntityRepository
{
    private $canonrepository;
    private $cnofficerepository;
    private $canonGSrepository;
    private $cnofficeGSrepository;

    
    public function __construct(ManagerRegistry $registry,
                                CanonRepository $canonrepository,
                                CnOfficeRepository $cnofficerepository,
                                CanonGSRepository $canonGSrepository,
                                CnOfficeGSRepository $cnofficeGSrepository) {
        $this->canonrepository = $canonrepository;
        $this->cnofficerepository = $cnofficerepository;
        $this->canonGSrepository = $canonGSrepository;
        $this->cnofficeGSrepository = $cnofficeGSrepository;

        parent::__construct($registry, CnOnline::class);
    }

    // /**
    //  * @return CnOnline[] Returns an array of CnOnline objects
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
    public function findOneBySomeField($value): ?CnOnline
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

        $qb = $this->createQueryBuilder('co')
                   ->select('COUNT(DISTINCT co.id)');
        
        $this->addQueryConditions($qb, $formmodel);

        $query = $qb->getQuery();

        $ncount = $query->getOneOrNullResult();
        return $ncount;
    }

    public function findByQueryObject(CanonFormModel $formmodel, $limit = 0, $offset = 0) {

        $qb = $this->createQueryBuilder('co');
                   # ->andWhere('co.id_dh IS NOT NULL'); # TODO remove this when data are complete
        
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


    private function addQueryConditions(QueryBuilder $qb, CanonFormModel $formmodel): QueryBuilder {

        # identifier
        if($formmodel->someid) {
            $db_id = Canon::extractDbId($formmodel->someid);
            $id_param = $db_id ? $db_id : $formmodel->someid;
            // dump($db_id, $id_param);

            $qb->andWhere(":someid = canon.id".
                          " OR :someid = canon.gsnId".
                          " OR :someid = canon.viafId".
                          " OR :someid = canon.wikidataId".
                          " OR :someid = canon.gndId")
               ->setParameter(':someid', $id_param);
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

        # office place
        if($formmodel->place) {
            // we have to join office a second time to filter at the level of persons
            $sort = 'yearatplace';
            $qb->join('canon.offices', 'oc_place')
               ->join('oc_place.monastery', 'm')
                ->andWhere('m.monastery_name LIKE :place')
                ->setParameter('place', '%'.$formmodel->place.'%');
        }
        # names
        if($formmodel->name) {
            $qb->join('co.namelookup', 'nlt')
                ->andWhere("CONCAT(nlt.givenname, ' ', nlt.prefixName, ' ', nlt.familyname) LIKE :qname".
                           " OR CONCAT(nlt.givenname, ' ', nlt.familyname)LIKE :qname".
                           " OR nlt.givenname LIKE :qname".
                           " OR nlt.familyname LIKE :qname")
               ->addOrderBy("nlt.familyname", "ASC")
               ->addOrderBy("nlt.givenname", "ASC")
               ->setParameter('qname', '%'.$formmodel->name.'%');
        }


        // TODO
        // $this->addFacets($formmodel, $qb);


        // for each individual person sort offices by start date in the template
        return $qb;
    }

    /*
      Fill the object `online` with data for the list view.
     */
    public function fillListData(CnOnline $online) {
        if (!is_null($online->getIdDh())) {
            $canon = $this->canonrepository->findOneById($online->getIdDh());
            $online->setCanonDh($canon);
            $officesdh = $this->cnofficerepository->findByIdCanonAndSort($online->getIdDh());
            $online->setOfficesDh($officesdh);
        }
        if (!is_null($online->getIdGs())) {
            $canon = $this->canonGSrepository->findOneById($online->getIdGs());
            $online->setCanonGs($canon);
            $officesgs = $this->cnofficeGSrepository->findByIdCanonAndSort($online->getIdGs());
            $online->setOfficesGs($officesgs);
        }

    }

    
}
