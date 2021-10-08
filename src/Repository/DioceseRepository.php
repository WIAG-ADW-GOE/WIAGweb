<?php

namespace App\Repository;

use App\Entity\Diocese;
use App\Entity\AltLabelDiocese;
use App\Entity\Place;
use App\Entity\Reference;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * @method Diocese|null find($id, $lockMode = null, $lockVersion = null)
 * @method Diocese|null findOneBy(array $criteria, array $orderBy = null)
 * @method Diocese[]    findAll()
 * @method Diocese[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DioceseRepository extends ServiceEntityRepository
{
    const GND_URL_TYPE_ID = '1';

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

    public function findWithBishopricSeat($idLong) {
        $id = DIOCESE::wiagidLongToId($idLong);

        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->leftJoin('diocese.bishopricseatobj', 'placeobj')
                   ->join('diocese.altlabel', 'altlabel');

        if(!is_null($id)) {
            $qb->andWhere('diocese.id_diocese = :id')
               ->setParameter('id', $id);
        }

        $query = $qb->getQuery();

        $diocese = $query->getOneOrNullResult();

        return $diocese;
    }

    public function findWithBishopricSeat_obs($idorname) {
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
                   ->select('COUNT(DISTINCT diocese.id_diocese) AS count')
                   ->join('diocese.altlabel', 'altlabel');

        if($name != "")
            $qb->andWhere('diocese.diocese LIKE :name OR altlabel.alt_label_diocese LIKE :name')
               ->setParameter('name', '%'.$name.'%');

        $query = $qb->getQuery();
        $count = $query->getOneOrNullResult();
        return $count ? $count['count'] : null;
    }

    /**
     * find dioceses by name with bishopric seat
     *
     * look for matching name in `diocese.diocese` and alt_label_diocese.alt_label_diocese
     */
    public function findByNameWithBishopricSeat($name = null, $limit = null, $offset = 0) {
        $qb = $this->createQueryBuilder('diocese')
                   ->addSelect('placeobj')
                   ->leftJoin('diocese.bishopricseatobj', 'placeobj')
                   ->join('diocese.altlabel', 'altlabel');

        if(!is_null($name) && $name != "") {
            $qb->andWhere('diocese.diocese LIKE :name OR altlabel.alt_label_diocese LIKE :name')
               ->setParameter('name', '%'.$name.'%');
        }

        if($limit) {
            $qb->orderBy('diocese.diocese')
               ->setFirstResult($offset)
               ->setMaxResults($limit);
        }

        $query = $qb->getQuery();

        $dioceses = new Paginator($query, true);
        # $dioceses = $query->getResult();

        return $dioceses;
    }

    public function getDioceseID($diocese) {
        if(is_null($diocese)) return null;
        $diocObj = $this->findByDiocese($diocese);
        $diocID = null;
        if($diocObj) {
            $diocID = $diocObj[0]->getWiagIdLong();

        }
        return $diocID;
    }

    public function findOneByGndId($id) {
        $qb = $this->createQueryBuilder('dc')
                   ->select('dc')
                   ->join('dc.external_urls', 'extern')
                   ->andWhere('extern.url_value = :id')
                   ->setParameter('id', $id)
                   ->andWhere('extern.url_type_id = '.self::GND_URL_TYPE_ID);

        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
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

        $suggestions = $query->getResult();

        $n_suggestions = count($suggestions);
        if ($n_suggestions < $limit) {
            $qb_a = $this->getEntityManager()
                       ->getRepository(AltLabelDiocese::class)
                       ->createQueryBuilder('ald')
                       ->select('DISTINCT ald.alt_label_diocese as suggestion')
                       ->andWhere('ald.alt_label_diocese LIKE :diocese')
                       ->setParameter('diocese', '%'.$diocese.'%')
                       ->setMaxResults($limit - $n_suggestions);
            $query = $qb_a->getQuery();
            $suggestions_a = $query->getResult();
            $suggestions = array_merge($suggestions, $suggestions_a);
        }

        return $suggestions;

    }


}
