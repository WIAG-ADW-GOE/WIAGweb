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

}
