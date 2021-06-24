<?php

namespace App\Service;

use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOffice;
use App\Entity\CnOnline;
use App\Entity\CnEra;
use App\Entity\CnNamelookup;
use App\Entity\CnOfficelookup;
use App\Entity\Monastery;
use App\Entity\CnIdlookup;

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
     * update/fill lookup tables for `canon`
     */
    public function setOnline($canon) {

        # fill/update
        # canon.date_hist_first
        # canon.date_hist_last
        # cn_office.location_show ()
        # cn_namelookup
        # cn_era
        # cn_officelookup
        # cn_idlookup

        $this->datesHist($canon);
        $co = $this->online($canon);
        $idonline = $co->getId();
        $this->era($idonline, $canon);
        $this->namelookup($co, $canon);
        $this->officelookup($co, $canon);
        $this->idlookup($co, $canon);
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
        // wiagid is based on $co.id_gs, $co.id_dh, $co.id_ep with increasing preference.
        // here id_gs is null, so use id_ep if present and id_dh otherwise
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

    /**
     * fill/update cn_officelookup
     */
    public function officelookup(CnOnline $co, Canon $canon) {
        $id_online = $co->getId();
        $rep = $this->em->getRepository(CnOfficelookup::class)
                        ->deleteByIdOnline($id_online);

        $offices = $canon->getOffices();

        foreach($offices as $o) {
            $olt = new CnOfficelookup();
            $olt->setId($o->getId())
                ->setCnOnline($co)
                ->setOfficeName($o->getOfficeName())
                ->setLocationName($o->getLocationShow());
            $monastery = $o->getMonastery();
            if (!is_null($monastery)) {
                $olt->setMonastery($monastery);
            }
            $olt->setArchdeaconTerritory($o->getArchdeaconTerritory())
                ->setNumdateStart($o->getNumDateStart())
                ->setNumdateEnd($o->getNumDateEnd());
            $this->em->persist($olt);

            $oltmonastery = $olt->getMonastery();
            # dump($oltmonastery);
        }
        if (!is_null($offices) && count($offices) > 0) {
            $this->em->flush();
        }
        # dd($co);

    }

    /**
     * fill/update cn_idlookup
     */
    public function idlookup(CnOnline $co, Canon $canon) {
        $id_online = $co->getId();
        $rep = $this->em->getRepository(CnIdlookup::class)
                        ->deleteByIdOnline($id_online);

        $episcid = $canon->getWiagEpiscId();
        $wiagid = $episcid ?? $canon->getWiagIdLong();
        $idl_wiagid = new CnIdlookup();
        $idl_wiagid->setCnOnline($co)
                   ->setAuthorityId($wiagid);
        $this->em->persist($idl_wiagid);

        $gsn = $canon->getGsnId();
        if (!is_null($gsn)) {
            $idl_gsn = new CnIdlookup();
            $idl_gsn->setCnOnline($co)
                      ->setAuthorityId($gsn);
            $this->em->persist($idl_gsn);
        }

        $gnd = $canon->getGndId();
        if (!is_null($gnd)) {
            $idl_gnd = new CnIdlookup();
            $idl_gnd->setCnOnline($co)
                      ->setAuthorityId($gnd);
            $this->em->persist($idl_gnd);
        }

        $viaf = $canon->getViafId();
        if (!is_null($viaf)) {
            $idl_viaf = new CnIdlookup();
            $idl_viaf->setCnOnline($co)
                      ->setAuthorityId($viaf);
            $this->em->persist($idl_viaf);
        }

        $wikidata = $canon->getWikidataId();
        if (!is_null($wikidata)) {
            $idl_wikidata = new CnIdlookup();
            $idl_wikidata->setCnOnline($co)
                          ->setAuthorityId($wikidata);
            $this->em->persist($idl_wikidata);
        }


        $this->em->flush();

    }



};
