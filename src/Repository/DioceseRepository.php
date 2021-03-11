<?php

namespace App\Repository;

use App\Entity\Diocese;
use App\Entity\Place;
use App\Entity\Reference;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Diocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method Diocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method Diocese[]    findAll()
 * @method Diocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DioceseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Diocese::class);
    }

    // /**
    //  * @return Diocese[] Returns an array of Diocese objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Diocese
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findWithBishopricSeat($idorname) {
        $id = DIOCESE::wiagidLongToId($idorname);
        $diocese = null;
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->leftJoin('diocese.bishopricseatobj', 'placeobj');

        if(preg_match('/[0-9]/', $id) > 0) {
            $qb->andWhere('diocese.id_diocese = :id')
               ->setParameter('id', $id);
        } else {
            $qb->andWhere('diocese.diocese = :id')
               ->setParameter('id', $id);
        }

        $query = $qb->getQuery();
        $diocese = $query->getOneOrNullResult();

        if ($diocese !== null) {
            $em = $this->getEntityManager();
            $reference = $em->getRepository(Reference::class)
                            ->find(Diocese::REFERENCE_ID);
            $diocese->setReference($reference);
        }

        return $diocese;
    }

    public function countByInitalletter($initialletter) {
        $qb = $this->createQueryBuilder('diocese')
                   ->select('COUNT(diocese.id_diocese) AS count');
        if($initialletter && $initialletter != 'A-Z') {
            $qb->andWhere('diocese.diocese LIKE :initialletter')
               ->setParameter('initialletter', $initialletter.'%');
        }
        $query = $qb->getQuery();
        $count = $query->getOneOrNullResult();
        return $count ? $count['count'] : null;
    }

    public function findByInitialLetterWithBishopricSeat($initialletter, $limit = null, $offset = 0) {
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->leftJoin('diocese.bishopricseatobj', 'placeobj');

        if($initialletter && $initialletter != 'A-Z') {
            $qb->andWhere('diocese.diocese LIKE :initialletter')
               ->setParameter('initialletter', $initialletter.'%');
        }

        if($limit) {
            $qb->orderBy('diocese.diocese')
               ->setFirstResult($offset)
               ->setMaxResults($limit);
        }

        $query = $qb->getQuery();
        $dioceses = $query->getResult();
        return $dioceses;
    }


    public function countByName($name) {
        $qb = $this->createQueryBuilder('diocese')
                   ->select('COUNT(diocese.id_diocese) AS count');

        if($name != "")
            $qb->andWhere('diocese.diocese LIKE :name')
               ->setParameter('name', '%'.$name.'%');

        $query = $qb->getQuery();
        $count = $query->getOneOrNullResult();
        return $count ? $count['count'] : null;
    }

    public function findByNameWithBishopricSeat($name, $limit = null, $offset = 0) {
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->leftJoin('diocese.bishopricseatobj', 'placeobj');

        if($name != "")
            $qb->andWhere('diocese.diocese LIKE :name')
               ->setParameter('name', '%'.$name.'%');

        if($limit) {
            $qb->orderBy('diocese.diocese')
               ->setFirstResult($offset)
               ->setMaxResults($limit);
        }

        $query = $qb->getQuery();
        $dioceses = $query->getResult();        
        
        return $dioceses;
    }

    public function getDioceseID($diocese) {
        if(is_null($diocese)) return null;
        $diocObj = $this->findByNameWithBishopricSeat($diocese);
        $diocID = null;
        if($diocObj) {
            $diocID = $diocObj[0]->getWiagIdLong();

        }
        return $diocID;
    }

    /* AJAX callback */
    public function suggestDiocese($diocese, $limit) {
        $qb = $this->createQueryBuilder('dc')
                   ->select('DISTINCT dc.diocese AS suggestion')
                   ->andWhere('dc.diocese LIKE :diocese')
                   ->setParameter('diocese', '%'.$diocese.'%')
                   ->setMaxResults($limit);
        $query = $qb->getQuery();

        # dd($query->getDQL());

        return $query->getResult();

    }


}
