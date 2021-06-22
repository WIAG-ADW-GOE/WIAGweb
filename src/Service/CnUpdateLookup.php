<?php

namespace App\Service;

use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOffice;
use App\Entity\CnOnline;
use App\Entity\CnEra;
use App\Entity\CnNamelookup;

use App\Entity\Person;
use App\Service\ParseDates;
use Doctrine\ORM\EntityManagerInterface;


class CnUpdateLookup {
    private $parsedates;
    private $em;

    public function __construct(ParseDates $parsedates,
                                EntityManagerInterface $em) {
        $this->parsedates = $parsedates;
        $this->em = $em;
    }

    /**
     * Scan offices and set date_hist_first and date_hist_last
     */
    public function datesHist(Canon $canon) {
        $datefirst = $this->parsedates->parse($canon->getDateBirth(), 'lower');
        $datelast = $this->parsedates->parse($canon->getDateDeath(), 'upper');

        foreach ($canon->getOffices() as $o) {

            $dateofficestart = $o->getNumDateStart();
            if (is_null($datefirst) || ($datefirst > $dateofficestart)) {
                $datefirst = $dateofficestart;
            }
            $dateofficeend = $o->getNumDateEnd();
            if (is_null($datelast) || ($datelast < $dateofficeend)) {
                $datelast = $dateofficeend;
            }
        }
        if (!is_null($datefirst)) {
            $canon->setDateHistFirst($datefirst);
        }
        if (!is_null($datelast)) {
            $canon->setDateHistLast($datelast);
        }
        return $canon;
    }

    /**
     * fill/update cn_online
     */
    public function online(Canon $canon) {
        $iddh = $canon->getId();
        $co = $this->em->getRepository(CnOnline::class)
                       ->findOneByIdDh($iddh);

        if (is_null($co)) {
            $co = new CnOnline;
            $co->setIdDh($iddh);
        }
        $this->fillOnline($co, $canon);

        $datadomstift = $this->em->getRepository(CnOffice::class)
                             ->findFirstDomstift($iddh);
        if (!is_null($datadomstift)) {
            $co->setDomstift($datadomstift[0]['name']);
            $co->setDomstiftStart($datadomstift[0]['numdate_start']);
        }


        $this->em->persist($co);
        $this->em->flush();

        return $co;
    }

    /**
     * aux
     */
    public function fillOnline(CnOnline $co, Canon $canon) {
        $gsn = $canon->getGsnId();
        $idgs = null;
        if (!is_null($gsn)) {
            $cngs = $this->em->getRepository(CanonGS::class)
                             ->findOneByGsnId($gsn);
            if (!is_null($cngs)) {
                $idgs = $cngs->getId();
            }
        }
        $co->setIdGs($idgs);
        $idep = $canon->getWiagEpiscId();
        $co->setIdEp($idep);
        $wiagid = null;
        if (!is_null($idep)) {
            $wiagid = Person::decorateId($idep);
        } else {
            $wiagid = $canon->getWiagIdLong();
        }
        $co->setWiagid($wiagid);
        $co->setGivenname($canon->getGivenname());
        $co->setFamilyname($canon->getFamilyname());

        return $co;
    }

    /**
     * fill/update cn_era
     */
    public function era($idonline, $canon) {
        $era = $this->em->getRepository(CnEra::class)
                        ->findOneByIdOnline($idonline);

        if (is_null($era)) {
            $era = new CnEra();
            $era->setIdOnline($idonline);
        }

        $era->setEraStart($canon->getDateHistFirst());
        $era->setEraEnd($canon->getDateHistLast());


        $this->em->persist($era);
        $this->em->flush();

        return($era);
    }

    /**
     * fill/update cn_namelookup
     */
    public function namelookup(CnOnline $co, Canon $canon) {
        $nl = $this->em->getRepository(CnNameLookup::class)
                       ->deleteByIdOnline($co->getId());

        $gn = $canon->getGivenname();
        $prefix = $canon->getPrefixName();
        $fn = $canon->getFamilyname();
        $this->makevariantsgn($co, $gn, $prefix, $fn);

        # givenname_variant with familyname
        $gnv = $canon->getGivennameVariant();
        $cgnv = array();
        if (!is_null($gnv)) {
            $cgnv = explode(",", $gnv);
            $cgnv = array_map('trim', $cgnv);
            foreach ($cgnv as $gnve) {
                $this->makevariantsgn($co, $gnve, $prefix, $fn);
            }
        }

        $fnv = $canon->getFamilynameVariant();
        if (!is_null($fnv)) {
            # givenname and givenname_variant with familyname_variant
            $cfnv = explode(",", $fnv);
            $cfnv = array_map('trim', $cfnv);
            foreach ($cfnv as $fnve) {
                $this->makevariantsgn($co, $gn, $prefix, $fnve);

                # givenname_variant with familyname_variant
                foreach ($cgnv as $gnve) {
                    $this->makevariantsgn($co, $gnve, $prefix, $fnve);
                }
            }
        }

        $this->em->flush();

        return null;
    }

    /**
     * return name variants for $gn (list of givennames with one or more elements)
     */
    public function makevariantsgn(CnOnline $co, $gn, $prefix, $fn) {

        $cgn = explode(" ", $gn);
        foreach ($cgn as $gnsingle) {
            $nl = new CnNameLookup();
            $nl->setCnOnline($co);
            $this->fillNamelookup($nl, $gnsingle, $prefix, $fn);
            $this->em->persist($nl);
        }

        # write complete set of givennames
        if (count($cgn) > 1) {
            $nl = new CnNameLookup();
            $nl->setCnOnline($co);
            $this->fillNamelookup($nl, $gn, $prefix, $fn);
            dump($nl);
            $this->em->persist($nl);
        }

    }

    public function fillNamelookup(CnNameLookup $nl, $gn, $prefix, $fn) {
        # skip the prefix if it is contained in the variant
        if (str_contains($fn, $prefix." ")) {
            $prefix = null;
        }
        $nl->setGivenname($gn);
        $nl->setPrefixName($prefix);
        $nl->setFamilyname($fn);
        if (!is_null($fn)) {
            $nl->setGnFn($gn." ".$fn);
        }
        if (!is_null($fn) && !is_null($prefix)) {
            $nl->setGnPrefixFn($gn." ".$prefix." ".$fn);
        }

    }



};
