<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Diocese;
use App\Service\RDFData;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;


class PersonLinkedData {

    const ID_PATH = 'id/';

    const GS_URL="http://personendatenbank.germania-sacra.de/index/gsn/";
    const GND_URL="http://d-nb.info/gnd/";
    const WIKIDATA_URL="https://www.wikidata.org/wiki/";
    const VIAF_URL="https://viaf.org/viaf/";

    const NAMESP_GND="https://d-nb.info/standards/elementset/gnd#";
    const NAMESP_SCHEMA="https://schema.org/";

    const JSONLDCONTEXT = [
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

    private $entitymanager;

    public function __construct(EntityManagerInterface $em, RDFData $rdfData) {
        $this->entitymanager = $em;
        $this->rdfData = $rdfData;
    }

    public function personToRdf($person, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);
        $personNode = $this->personToLinkedData($person, $baseurl);

        $xmlroot = RDFData::xmlroot($personNode);
        $rdf = $serializer->serialize($xmlroot, 'xml', RDFData::XML_CONTEXT);

        return $rdf;
    }

    public function personsToRdf($persons, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $personNodes = array();
        foreach($persons as $person) {
            array_push($personNodes, ...$this->personToLinkedData($person, $baseurl));
        }
        $xmlroot = RDFData::xmlroot($personNodes);
        $rdf = $serializer->serialize($xmlroot, 'xml', RDFData::XML_CONTEXT);

        return $rdf;
    }

    public function personToLinkedData($person, $baseurl) {
        $pld = array();

        $gfx = "gndo:";
        $owlfx = "owl:";
        $foaffx = "foaf:";
        $scafx = "schema:";

        $idpath = $baseurl.'/'.self::ID_PATH;

        $pld = [
            'rdf:type' => [
                '@rdf:resource' => self::NAMESP_GND.'DifferentiatedPerson'
                ]
        ];

        $personID = $person->getWiagidLong();

        $fn = $person->getFamilyname();
        $fndt = RDFData::xmlStringData($fn);

        $gn = $person->getGivenname();
        $gndt = RDFData::xmlStringData($gn);

        $prefixname = $person->getPrefixName();
        $prefixdt = RDFData::xmlStringData($prefixname);

        $aname = array_filter([$gn, $prefixname, $fn],
                              function($v){return $v !== null;});
        $pld[$gfx.'preferredName'] = RDFData::xmlStringData(implode(' ', $aname));

        $pfeftp[$gfx.'forename'] = $gndt;
        if($prefixname)
            $pfeftp[$gfx.'prefix'] = $prefixdt;
        if($fn)
            $pfeftp[$gfx.'surname'] = $fndt;

        $pld[$gfx.'preferredNameEntityForThePerson'] = RDFData::blankNode(array($pfeftp));

        $gnv = $person->getGivennameVariant();

        $vneftps = array();
        /* one or more variants for the given name */
        if($gnv) {
            $gnvs = explode(',', $gnv);
            foreach($gnvs as $gnvi) {
                $vneftp = [];
                $vneftp[$gfx.'forename'] = RDFData::xmlStringData(trim($gnvi));
                if($prefixname)
                    $vneftp[$gfx.'prefix'] = $prefixdt;
                if($fn)
                    $vneftp[$gfx.'surname'] = $fndt;
                $vneftps[] = $vneftp;
            }
        }

        $fnv = $person->getFamilynameVariant();
        if($fnv) {
            $fnvs = explode(',', $fnv);
            foreach($fnvs as $fnvi) {
                $vneftp = [];
                $vneftp[$gfx.'forename'] = $gndt;
                if($prefixname)
                    $vneftp[$gfx.'prefix'] = $prefixdt;
                $vneftp[$gfx.'surname'] = RDFData::xmlStringData(trim($fnvi));
                $vneftps[] = $vneftp;
            }
        }

        if($gnv && $fnv) {
            $gnvs = explode(',', $gnv);
            $fnvs = explode(',', $fnv);
            foreach($fnvs as $fnvi) {
                foreach($gnvs as $gnvi) {
                    $vneftp = [];
                    $vneftp[$gfx.'forename'] = RDFData::xmlStringData($gnvi);
                    if($prefixname)
                        $vneftp[$gfx.'prefix'] = $prefixdt;
                    $vneftp[$gfx.'surname'] = RDFData::xmlStringData(trim($fnvi));
                    $vneftps[] = $vneftp;
                }
            }
        }

        /* Set 'variantNameEntityForThePerson' as string or array */
        if(count($vneftps) > 0)
            $pld[$gfx.'variantNameEntityForThePerson'] = RDFData::blankNode($vneftps);


        $fv = $person->getCommentPerson();
        if($fv)
            $pld[$gfx.'biographicalOrHistoricalInformation'] = RDFData::xmlStringData($fv);

        $fv = $person->getDateBirth();
        if($fv) $pld[$gfx.'dateOfBirth'] = RDFData::xmlStringData($fv);

        $fv = $person->getDateDeath();
        if($fv) $pld[$gfx.'dateOfDeath'] = RDFData::xmlStringData($fv);

        // $fv = $person->getReligiousOrder();
        // if($fv) $pld['religiousOrder'] = $fv;


        $fv = $person->getGndid();
        if($fv) $pld[$gfx.'gndIdentifier'] = RDFData::xmlStringData($fv);

        $exids = array();
        if($person->hasExternalIdentifier() || $person->hasOtherIdentifier()) {
            $fv = $person->getGsid();
            if($fv) $exids[] = [
                '@rdf:resource' => self::GS_URL.$fv,
            ];

            $fv = $person->getGndid();
            if($fv) $exids[] = [
                '@rdf:resource' => self::GND_URL.$fv,
            ];

            $fv = $person->getViafid();
            if($fv) $exids[] = [
                '@rdf:resource' => self::VIAF_URL.$fv,
            ];

            $fv = $person->getWikidataid();
            if($fv) $exids[] = [
                '@rdf:resource' => self::WIKIDATA_URL.$fv,
            ];
        }

        if(count($exids) > 0)
            $pld[$owlfx.'sameAs'] =
                       count($exids) > 1 ? $exids : $exids[0];

        $fv = $person->getWikipediaurl();

        if($fv) $pld[$foaffx.'page'] = $fv;

        $descName = [
            '@rdf:about' => $idpath.$personID,
            '#' => $pld,
        ];


        $offices = $person->getOffices();
        $descOffices = array();
        if($offices) {
            foreach($offices as $oc) {
                $roleNodeID = uniqid('role');
                $descOffices[] = [
                    '@rdf:about' => $idpath.$personID,
                    '#' => [
                        $scafx.'hasOccupation' => [
                            '@rdf:nodeID' => $roleNodeID
                        ]
                    ]
                ];
                $descOffices[] = $this->roleNode($oc, $roleNodeID, $idpath);
            }
        }

        return array_merge([$descName], $descOffices);

        // $fv = $person->getReference();
        // if($fv) {
        //     $pld['reference'] = $fv->toArray();
        //     $fiv = $person->getPagesGatz();
        //     if($fiv)
        //         $pld['reference']['pages'] = $fiv;
        // }

    }

    public function roleNode($office, $roleNodeID, $idpath) {
        $scafx = "schema:";
        $gfx = "gndo:";

        $ocld['rdf:type'] = [
            '@rdf:resource' => self::NAMESP_SCHEMA.'Role'
        ];

        $ocld[$scafx.'roleName'] = RDFData::xmlStringData($office->getOfficeName());

        $fv = $office->getDateStart();
        if($fv) $ocld[$scafx.'startDate'] = RDFData::xmlStringData($fv);

        $fv = $office->getDateEnd();
        if($fv) $ocld[$scafx.'endDate'] = RDFData::xmlStringData($fv);

        $fv = $office->getDiocese();
        if($fv) {
            $dioceseRepository = $this->entitymanager->getRepository(Diocese::class);
            $dioceseID = $dioceseRepository->getDioceseID($fv);
            if($dioceseID)
                $ocld[$gfx.'affiliation'] = [
                    '@rdf:resource' => $idpath.$dioceseID
                ];
            $ocld[$scafx.'description'] = RDFData::xmlStringData($fv);
        }

        $fv = $office->getMonastery();
        if($fv) $ocld[$scafx.'description'] = RDFData::xmlStringData($fv->getMonasteryName());

        $roleNode = [
            '@rdf:nodeID' => $roleNodeID,
            '#' => $ocld,
        ];

        return $roleNode;
    }

    public function personToJSONLD($person, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $personNode = $this->personToJSONLinkedData($person, $baseurl);
        $jsondata = array_merge(self::JSONLDCONTEXT, $personNode);
        $json = $serializer->serialize($jsondata, 'json');

        return $json;
    }

    public function personsToJSONLD($persons, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $personNodes = self::JSONLDCONTEXT;
        foreach($persons as $person) {
            array_push($personNodes, $this->personToJSONLinkedData($person, $baseurl));
        }

        $json = $serializer->serialize($personNodes, 'json');

        return $json;
    }

    public function personToJSONLinkedData($person, $baseurl) {
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
                $nodesOffices[] = $this->jsonRoleNode($oc, $idpath);
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

    public function jsonRoleNode($office, $idpath) {
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
            $dioceseRepository = $this->entitymanager->getRepository(Diocese::class);
            $dioceseID = $dioceseRepository->getDioceseID($diocese);
            if($dioceseID) $ocld[$gndfx.'affiliation'] = $idpath.$dioceseID;
        }

        $fv = $office->getMonastery();
        if($fv) $ocld[$scafx.'description'] = $fv->getMonasteryName();

        return $ocld;
    }

};
