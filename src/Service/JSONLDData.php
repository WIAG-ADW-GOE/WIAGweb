<?php

namespace App\Service;

use App\Entity\Diocese;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;


class JSONLDData {

    const ID_PATH = 'id/';

    const GS_URL="http://personendatenbank.germania-sacra.de/index/gsn/";
    const GND_URL="http://d-nb.info/gnd/";
    const WIKIDATA_URL="https://www.wikidata.org/wiki/";
    const VIAF_URL="https://viaf.org/viaf/";

    const NAMESP_GND="https://d-nb.info/standards/elementset/gnd#";
    const NAMESP_SCHEMA="https://schema.org/";


    private $entitymanager;

    public function __construct(EntityManagerInterface $em) {
        $this->entitymanager = $em;
    }

    public function jsonldcontext() {
        $context = [
                "@context" => [
                    "@version" => 1.1,
                    "xsd" => "http://www.w3.org/2001/XMLSchema#",
                    "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
                    "foaf" => "http://xmlns.com/foaf/0.1/",
                    "gndo" => "https://d-nb.info/standards/elementset/gnd#",
                    "schema" => "https://schema.org/",
                    "variantNamesByLang" => [
                        "@id" => "https://d-nb.info/standards/elementset/gnd#variantName",
                        "@container" => "@language",
                    ],
                ],
            ];
        return $context;
    }

    public function personToJSONLD($person, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $personNode = $this->personToLinkedData($person, $baseurl);
        $context = $this->jsonldcontext();
        $jsondata = array_merge($context, $personNode);
        $json = $serializer->serialize($jsondata, 'json');

        return $json;
    }

    public function personsToJSONLD($persons, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $personNodes = $this->jsonldcontext();
        foreach($persons as $person) {
            array_push($personNodes, $this->personToLinkedData($person, $baseurl));
        }

        $json = $serializer->serialize($personNodes, 'json');

        return $json;
    }

    public function personToLinkedData($person, $baseurl) {
        $pld = array();

        $gfx = "gndo:";
        $owlfx = "owl:";
        $foaffx = "foaf:";
        $scafx = "schema:";

        $idpath = $baseurl.'/'.self::ID_PATH;

        // $persondetails = [
        //         "http://wiag-vocab.adw-goe.de/10891" => [
        //             "gndo:preferredName" => "Ignaz Heinrich Wessenberg",
        //             "gndo:preferredNameEntityForThePerson"=> [
        //                 "gndo:forename"=>"Ignaz Heinrich",
        //                 "gndo:prefix"=>"von",
        //                 "gndo:surname"=>"Wessenberg"
        //             ]
        //         ]
        //     ];

        // return $persondetails;

        $personID = [
            "@id" => $person->getWiagidLong(),
            "@type" => $gfx."DifferentiatedPerson",
        ];

        $fn = $person->getFamilyname();

        $gn = $person->getGivenname();

        $prefixname = $person->getPrefixName();

        $aname = array_filter([$gn, $prefixname, $fn],
                              function($v){return $v !== null;});
        $pld[$gfx.'preferredName'] = implode(' ', $aname);

        $pfeftp[$gfx.'forename'] = $gn;
        if($prefixname)
            $pfeftp[$gfx.'prefix'] = $prefixname;
        if($fn)
            $pfeftp[$gfx.'surname'] = $fn;

        $pld[$gfx.'preferredNameEntityForThePerson'] = $pfeftp;


        $gnv = $person->getGivennameVariant();

        $vneftps = array();
        /* one or more variants for the given name */
        if($gnv) {
            $gnvs = explode(',', $gnv);
            foreach($gnvs as $gnvi) {
                $vneftp = [];
                $vneftp[$gfx.'forename'] = trim($gnvi);
                if($prefixname)
                    $vneftp[$gfx.'prefix'] = $prefixname;
                if($fn)
                    $vneftp[$gfx.'surname'] = $fn;
                $vneftps[] = $vneftp;
            }
        }

        $fnv = $person->getFamilynameVariant();
        /* one or more variants for the familyname */
        if($fnv) {
            $fnvs = explode(',', $fnv);
            foreach($fnvs as $fnvi) {
                $vneftp = [];
                $vneftp[$gfx.'forename'] = $gn;
                if($prefixname)
                    $vneftp[$gfx.'prefix'] = $prefixname;
                $vneftp[$gfx.'surname'] = trim($fnvi);
                $vneftps[] = $vneftp;
            }
        }

        if($gnv && $fnv) {
            $gnvs = explode(',', $gnv);
            $fnvs = explode(',', $fnv);
            foreach($fnvs as $fnvi) {
                foreach($gnvs as $gnvi) {
                    $vneftp = [];
                    $vneftp[$gfx.'forename'] = $gnvi;
                    if($prefixname)
                        $vneftp[$gfx.'prefix'] = $prefixname;
                    $vneftp[$gfx.'surname'] = trim($fnvi);
                    $vneftps[] = $vneftp;
                }
            }
        }

        /* Set 'variantNameEntityForThePerson' */
        if(count($vneftps) > 0)
            $pld[$gfx.'variantNameEntityForThePerson'] = $vneftps;

        $fv = $person->getCommentPerson();
        if($fv)
            $pld[$gfx.'biographicalOrHistoricalInformation'] = $fv;

        $fv = $person->getDateBirth();
        if($fv) $pld[$gfx.'dateOfBirth'] = $fv;

        $fv = $person->getDateDeath();
        if($fv) $pld[$gfx.'dateOfDeath'] = $fv;

        // $fv = $person->getReligiousOrder();
        // if($fv) $pld['religiousOrder'] = $fv;


        $fv = $person->getGndid();
        if($fv) $pld[$gfx.'gndIdentifier'] = $fv;

        $exids = array();
        if($person->hasExternalIdentifier() || $person->hasOtherIdentifier()) {
            $fv = $person->getGsid();
            if($fv) $exids[] = self::GS_URL.$fv;

            $fv = $person->getGndid();
            if($fv) $exids[] = self::GND_URL.$fv;

            $fv = $person->getViafid();
            if($fv) $exids[] = self::VIAF_URL.$fv;

            $fv = $person->getWikidataid();
            if($fv) $exids[] = self::WIKIDATA_URL.$fv;
        }

        if(count($exids) > 0)
            $pld[$owlfx.'sameAs'] =
                       count($exids) > 1 ? $exids : $exids[0];

        $fv = $person->getWikipediaurl();

        if($fv) $pld[$foaffx.'page'] = $fv;

        /* offices */
        $offices = $person->getOffices();
        $nodesOffices = array();
        $pldOffices = array();
        if($offices && count($offices) > 0) {
            foreach($offices as $oc) {
                $nodesOffices[] = $this->roleNode($oc, $idpath);
            }
            $pld[$scafx.'hasOccupation'] = $nodesOffices;
        }

        return array_merge($personID, $pld);

        // $fv = $person->getReference();
        // if($fv) {
        //     $pld['reference'] = $fv->toArray();
        //     $fiv = $person->getPagesGatz();
        //     if($fiv)
        //         $pld['reference']['pages'] = $fiv;
        // }

    }


    public function getDioceseID($diocese) {
        if(is_null($diocese)) return null;
        $diocRepository = $this->entitymanager->getRepository(Diocese::class);
        $diocObj = $diocRepository->findByNameWithBishopricSeat($diocese);
        $diocID = null;
        if($diocObj) {
            $diocID = $diocObj[0]->getWiagIdLong();

        }
        return $diocID;
    }

    public function roleNode($office, $idpath) {
        $scafx = "schema:";
        $gndfx = "gndo:";

        // $ocld['@id'] = $roleNodeID;
        $ocld['@type'] = self::NAMESP_SCHEMA.'Role';

        $ocld[$scafx.'roleName'] = $office->getOfficeName();

        $fv = $office->getDateStart();
        if($fv) $ocld[$scafx.'startDate'] = $fv;

        $fv = $office->getDateEnd();
        if($fv) $ocld[$scafx.'endDate'] = $fv;

        $diocese = $office->getDiocese();
        if($diocese) {
            $ocld[$scafx.'description'] = $diocese;
            $dioceseID = $this->getDioceseID($diocese);
            if($dioceseID) $ocld[$gndfx.'affiliation'] = $idpath.$dioceseID;
        }


        $fv = $office->getMonastery();
        if($fv) $ocld[$scafx.'description'] = $fv->getMonasteryName();

        return $ocld;
    }


    public function dioceseToJSONLD($diocese, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $dioceseNode = $this->dioceseToLinkedData($diocese, $baseurl);
        $context = $this->jsonldcontext();
        $jsondata = array_merge($context, $dioceseNode);
        $json = $serializer->serialize($jsondata, 'json');

        return $json;
    }

    public function diocesesToJSONLD($dioceses, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = $this->jsonldcontext();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseToLinkedData($diocese, $baseurl));
        }

        $json = $serializer->serialize($dioceseNodes, 'json');

        return $json;
    }

    public function dioceseToLinkedData($diocese, $baseurl) {
        $dld = array();

        $gfx = "gndo:";
        $owlfx = "owl:";
        $foaffx = "foaf:";
        $scafx = "schema:";

        $idpath = $baseurl.'/'.self::ID_PATH;

        $dioceseID = [
            "@id" => $idpath.$diocese->getWiagidLong(),
            "@type" => $gfx."ReligiousAdministrativeUnit",
        ];


        $dioceseName = $diocese->getDiocese();
        $dioceseStatus = $diocese->getDioceseStatus();

        $dld[$gfx.'preferredName'] = $dioceseStatus.' '.$dioceseName;

                $fv = $diocese->getDateOfFounding();
        if($fv) $dld[$gfx.'dateOfEstablishment'] = $fv;

        $fv = $diocese->getDateOfDissolution();
        if($fv) $dld[$gfx.'dateOfTermination'] = $fv;

        $fv = $diocese->getAltlabel();
        if($fv) {
            $clabel = array();
            foreach($fv as $label) {
                $altlabel = $label->toArray();
                $name = $altlabel['name'];
                $lang = $altlabel['lang'];
                $clabel[$lang][] = $name;
            }
            $dld['variantNamesByLang'] = $clabel;
        }

        $note = $diocese->getNoteDiocese();
        $noteSeat = $diocese->getNoteBishopricSeat();
        if($note) {
            $noteout = $note;
            if($noteSeat) $noteout = $noteout.' '.$noteSeat;
            $dld[$scafx.'description'] = $note;
        }
        elseif($noteSeat) {
            $dld[$scafx.'description'] = $noteSeat;
        }

        $fv = $diocese->getExternalUrls();
        if($fv) {
            $cei = array();
            foreach($fv as $extid) {
                $authority = $extid->getAuthority()->getUrlNameFormatter();
                $extidurl = $extid->getUrlValue();
                $baseurl = $extid->getAuthority()->getUrlFormatter();
                $extidurl = $baseurl.$extidurl;

                if($authority == "Wikipedia-Artikel") {
                    $dld[$foaffx.'page'] = $extidurl;
                }
                else {
                    $cei[] = $extidurl;
                }

            }
            $dld[$owlfx.'sameAs'] = $cei;
        }


        return array_merge($dioceseID, $dld);
    }

};
