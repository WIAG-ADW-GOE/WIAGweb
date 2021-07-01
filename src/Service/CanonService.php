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
use App\Entity\CnReference;
use App\Entity\CnCanonReference;
use App\Entity\Domstift;

use App\Entity\Person;
use App\Service\ParseDates;
use Doctrine\ORM\EntityManagerInterface;


class CanonService {
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
        // cleanup tables; set tables for canon from GS (see canon lookup table).
        $this->unsetOnline($canon);

        $gsn_id = $canon->getGsnId();
        $canon_gs = null;
        $id_gs = null;
        if (!is_null($gsn_id)) {
            $canon_gs = $this->em->getRepository(CanonGS::class)
                                ->findOneByGsnId($gsn_id);
            $id_gs = $canon_gs->getId();
        }
        $co = null;
        // check if there is an entry from GS
        if (!is_null($id_gs)) {
            // There should be an entry in the canon lookup table
            $co = $this->em->getRepository(CnOnline::class)
                           ->findOneByIdGs($id_gs);
            if (is_null($co)) {
                // This should not happen
                // TODO log this situation
            }
        }

        // no entry from GS or data mismatch
        $era = null;
        if (is_null($co)) {
            $era = new CnEra();
            $co = new CnOnline();
        } else {
            $era = $co->getEra();
        }
        $co->setIdDh($canon->getId());

        // online
        $this->online($co, $canon);
        $idonline = $co->getId();
        if (is_null($era->getIdOnline())) {
            $era->setIdOnline($idonline);
        }
        // era
        // if there is a canon from GS, then it's data are already stored
        // in $co->era.
        $this->era($era, $canon);
        // office
        $this->officelookup($co, $canon);
        // name
        $this->namelookup($co, $canon);
        // id
        $this->idlookup($co, $canon);
        #dd($co);
    }

    /**
     * clear lookup tables for `canon`
     */
    public function unsetOnline($canon) {
        // clear everything for $canon
        // restore tables for $canon from GS

        $id_dh = $canon->getId();

        $co = $this->em->getRepository(CnOnline::class)
                       ->findOneByIdDh($id_dh);

        // it is possible that $canon never was online
        if (!is_null($co)) {
            $id_online = $co->getId();
            $id_gs = $co->getIdGs();
            $co->setIdDh(null);

            // id
            $this->em->getRepository(CnIdlookup::class)
                     ->deleteByIdOnline($id_online);
            // name
            $this->em->getRepository(CnNameLookup::class)
                     ->deleteByIdOnline($id_online);
            // office
            $this->em->getRepository(CnOfficelookup::class)
                     ->deleteByIdOnline($id_online);
            // era
            $this->clearEra($co->getEra());

            // restore entries for canon from GS
            $canon_gs = null;
            if (!is_null($id_gs)) {
                $canon_gs = $this->em->getRepository(CanonGS::class)
                                     ->findOneById($id_gs);
            }
            if (!is_null($canon_gs)) {
                $this->online($co, $canon_gs);
                $this->officelookup($co, $canon_gs);
                $this->era($co->getEra(), $canon_gs);
                $this->idlookup($co, $canon_gs);
                $this->namelookup($co, $canon_gs);

            } else {
                // canon was online
                $this->em->remove($co->getEra());
                $this->em->remove($co);
                $this->em->flush();
            }
        }
    }

    /**
     * set numeric values
     */
    public function setNumdates(Canon $canon) {
        $pd = $this->parsedates;
        $canon->setNumdateBirth($pd->parse($canon->getDateBirth(), 'lower'));
        $canon->setNumdateDeath($pd->parse($canon->getDateDeath(), 'upper'));
    }


    /**
     * Scan offices and set date_hist_first and date_hist_last
     * obsolete 2021-06-29
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
     * fill/update canon lookup table
     */
    public function online($co, $canon) {
        $co->setIdEp($canon->getWiagEpiscId())
           ->setWiagid($canon->getWiagIdLong())
           ->setGivenname($canon->getGivenname())
           ->setFamilyname($canon->getFamilyname());

        $this->em->persist($co);
        $this->em->flush();

        return $co;
    }

    public function clearEra(CnEra $era) {
        $era->setEraStart(null)
            ->setEraEnd(null)
            ->setDomstift(null)
            ->setDomstiftStart(null);
        return $era;
    }

    /**
     * fill/update era lookup table
     */
    public function era(CnEra $era, $canon) {
        $era_start = $era->getEraStart();
        $date_first = $canon->getNumDateBirth();
        if (!is_null($date_first)
            && (is_null($era_start) || $era_start > $date_first)) {
            $era_start = $date_first;
        }

        $era_end = $era->getEraEnd();
        $date_last = $canon->getNumDateDeath();
        if (!is_null($date_last)
            && (is_null($era_end) || $era_end < $date_last)) {
            $era_end = $date_last;
        }

        // offices
        $era_domstift = $era->getDomstift();
        $era_domstift_start = $era->getDomstiftStart();
        foreach ($canon->getOffices() as $o) {
            $date_start = $o->getNumdateStart();
            #dump($date_start);
            if (!is_null($date_start)
                && (is_null($era_start) || $era_start > $date_start)) {
                $era_start = $date_start;
            }

            $date_end = $o->getNumdateEnd();
            if (!is_null($date_end)
                && (is_null($era_end) || $era_end < $date_end)) {
                $era_end = $date_end;
            }

            $monastery = $o->getMonastery();
            $domstift = null;
            $n_ds = 0;
            if (!is_null($monastery)) {
                $n_ds = $this->em->getRepository(Domstift::class)
                                 ->countByGsId($monastery->getWiagId());
            }
            if ($n_ds > 0) {
                if (is_null($era_domstift_start) || $era_domstift_start > $date_start) {
                    $era_domstift = $monastery->getMonasteryName();
                    $era_domstift_start = $date_start;
                }
            }
        }

        $era->setEraStart($era_start);
        $era->setEraEnd($era_end);
        $era->setDomstift($era_domstift);
        $era->setDomstiftStart($era_domstift_start);

        $this->em->persist($era);
        $this->em->flush();
        #dump($era);

        return($era);
    }

    /**
     * fill/update cn_namelookup
     */
    public function namelookup(CnOnline $co, $canon) {
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
    public function officelookup(CnOnline $co, $canon) {

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

        }
        if (!is_null($offices) && count($offices) > 0) {
            $this->em->flush();
        }
    }


    /**
     * fill/update cn_idlookup
     */
    public function idlookup(CnOnline $co, $canon) {

        $wiagid = $canon->getWiagIdLong();
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

    /**
     * fill/update references
     */
    public function canonreference(Canon $canon) {
        # TODO manage merged canons
        $references = $canon->getReferences();
        foreach ($references as $refi) {
            $this->em->remove($refi);
        }
        $this->em->flush();

        $refobj = $this->em->getRepository(CnReference::class)
                           ->find($canon->getIdReference());

        if (!is_null($refobj)) {
            $ref = new CnCanonReference();
            $ref->setCanon($canon);
            $ref->setReference($refobj);
            $ref->setPageReference($canon->getPageReference());
            $ref->setIdInReference($canon->getIdInReference());

            $this->em->persist($ref);
            $this->em->flush();
        }
    }

    /**
     * collect ids of canons that are merged to $canon
     */
    public function collectMerged($cmerged, $id) {
        $children = $this->em->getRepository(Canon::class)
                             ->findMerged($id);
        if (count($children) > 0) {
            $children_ids = array_column($children, 'id');
            // avoid circles
            $children_ids = array_filter($children_ids,
                                         function($e) use ($id) {
                                             return $e != $id;
                                         });
            foreach ($children_ids as $cid) {
                $cil = $this->collectMerged($cmerged, $cid);
                $cmerged = array_merge($cmerged, $cil);
            }
            $cmerged = array_merge($cmerged, $children_ids);
        }
        return $cmerged;
    }


};
