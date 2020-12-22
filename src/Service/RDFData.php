<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Diocese;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;


class RDFData {

    const ID_PATH = 'id/';
    
    const GS_URL="http://personendatenbank.germania-sacra.de/index/gsn/";
    const GND_URL="http://d-nb.info/gnd/";
    const WIKIDATA_URL="https://www.wikidata.org/wiki/";
    const VIAF_URL="https://viaf.org/viaf/";

    const NAMESP_GND="https://d-nb.info/standards/elementset/gnd#";
    const NAMESP_SCHEMA="https://schema.org/";

    const WIAG_PREFIX="wiag";
    const GND_PREFIX="gndo";
    const OWL_PREFIX="owl";
    const FOAF_PREFIX="foaf";
    const SCHEMA_PREFIX="schema";

    const XML_CONTEXT= [
            'xml_format_output' => true,
            'xml_root_node_name' => 'rdf:RDF',
            'xml_encoding' => 'utf-8',
    ];


    private $entitymanager;

    public function __construct(EntityManagerInterface $em) {
        $this->entitymanager = $em;
    }

    public function personToRdf($person, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);
        $personNode = $this->personToLinkedData($person, $baseurl);
                 
        $xmlroot = $this->xmlroot($personNode);
        $rdf = $serializer->serialize($xmlroot, 'xml', self::XML_CONTEXT);

        return $rdf;
    }

    public function personsToRdf($persons, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);
        
        $personNodes = array();
        foreach($persons as $person) {
            array_push($personNodes, ...$this->personToLinkedData($person, $baseurl));
        }
        $xmlroot = $this->xmlroot($personNodes);
        $rdf = $serializer->serialize($xmlroot, 'xml', self::XML_CONTEXT);

        return $rdf;
    }

     
    public function xmlroot($data) {

        $node = [
            '@xmlns:rdf' => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
            '@xmlns:'.self::OWL_PREFIX => "http://www.w3.org/2002/07/owl#",
            '@xmlns:schema' => "http://schema.org/",
            '@xmlns:'.self::GND_PREFIX => self::NAMESP_GND,
            '@xmlns:'.self::FOAF_PREFIX => "http://xmlns.com/foaf/0.1/",
            '@xmlns:'.self::SCHEMA_PREFIX => "http://http://schema.org/",
            '#' => [
                'rdf:Description' => $data
                ]
        ];
        return $node;
    }

    public function personToArray(Person $person) {
        $pj = array();
        $pj['wiagId'] = $person->getWiagidLong();

        $fv = $person->getFamilyname();
        if($fv) $pj['familyName'] = $fv;

        $pj['givenName'] = $person->getGivenname();

        $fv = $person->getPrefixName();
        if($fv) $pj['prefix'] = $fv;

        $fv = $person->getFamilynameVariant();
        if($fv) $pj['variantFamilyName'] = $fv;

        $fv = $person->getGivennameVariant();
        if($fv) $pj['variantGivenName'] = $fv;

        $fv = $person->getCommentName();
        if($fv) $pj['comment_name'] = $fv;

        $fv = $person->getCommentPerson();
        if($fv) $pj['comment_person'] = $fv;

        $fv = $person->getGivennameVariant();
        if($fv) $pj['variantGivenName'] = $fv;


        $fv = $person->getDateBirth();
        if($fv) $pj['dateOfBirth'] = $fv;

        $fv = $person->getDateDeath();
        if($fv) $pj['dateOfDeath'] = $fv;

        // $fv = $person->getReligiousOrder();
        // if($fv) $pj['religiousOrder'] = $fv;

        if($person->hasExternalIdentifier() || $person->hasOtherIdentifier()) {
            $pj['identifier'] = array();
            $nd = &$pj['identifier'];
            $fv = $person->getGsid();
            if($fv) $nd['gsId'] = $fv;

            $fv = $person->getGndid();
            if($fv) $nd['gndId'] = $fv;

            $fv = $person->getViafid();
            if($fv) $nd['viafId'] = $fv;

            $fv = $person->getWikidataid();
            if($fv) $nd['wikidataId'] = $fv;

            $fv = $person->getWikipediaurl();
            if($fv) $nd['wikipediaUrl'] = $fv;
        }

        $offices = $person->getOffices();
        if($offices) {
            $pj['offices'] = array();
            $ocJSON = &$pj['offices'];
            foreach($offices as $oc) {
                $ocJSON[] = $oc->toArray();
            }
        }

        $fv = $person->getReference();
        if($fv) {
            $pj['reference'] = $fv->toArray();
            $fiv = $person->getPagesGatz();
            if($fiv)
                $pj['reference']['pages'] = $fiv;
        }

        return $pj;
    }

    public function personToLinkedData($person, $baseurl) {
        $pld = array();
        $gfx = self::GND_PREFIX.':';
        $owlfx = self::OWL_PREFIX.':';
        $foaffx = self::FOAF_PREFIX.':';
        $scafx = self::SCHEMA_PREFIX.':';

        $idpath = $baseurl.'/'.self::ID_PATH;
        
        $pld = [
            'rdf:type' => [
                '@rdf:resource' => self::NAMESP_GND.'DifferentiatedPerson'
                ]
        ];

        $personID = $person->getWiagidLong();

        $fn = $person->getFamilyname();
        $fndt = $this->xmlStringData($fn);

        $gn = $person->getGivenname();
        $gndt = $this->xmlStringData($gn);

        $prefixname = $person->getPrefixName();
        $prefixdt = $this->xmlStringData($prefixname);

        $aname = array_filter([$gn, $prefixname, $fn],
                              function($v){return $v !== null;});
        $pld[$gfx.'preferredName'] = $this->xmlStringData(implode(' ', $aname));

        $pfeftp[$gfx.'forename'] = $gndt;
        if($prefixname)
            $pfeftp[$gfx.'prefix'] = $prefixdt;
        if($fn)
            $pfeftp[$gfx.'surname'] = $fndt;

        $pld[$gfx.'preferredNameEntityForThePerson'] = $this->blankNode(array($pfeftp));

        $gnv = $person->getGivennameVariant();

        $vneftps = array();
        /* one or more variants for the given name */
        if($gnv) {
            $gnvs = explode(',', $gnv);
            foreach($gnvs as $gnvi) {
                $vneftp = [];
                $vneftp[$gfx.'forename'] = $this->xmlStringData(trim($gnvi));
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
                $vneftp[$gfx.'surname'] = $this->xmlStringData(trim($fnvi));
                $vneftps[] = $vneftp;
            }
        }

        if($gnv && $fnv) {
            $gnvs = explode(',', $gnv);
            $fnvs = explode(',', $fnv);
            foreach($fnvs as $fnvi) {
                foreach($gnvs as $gnvi) {
                    $vneftp = [];
                    $vneftp[$gfx.'forename'] = $this->xmlStringData($gnvi);
                    if($prefixname)
                        $vneftp[$gfx.'prefix'] = $prefixdt;
                    $vneftp[$gfx.'surname'] = $this->xmlStringData(trim($fnvi));
                    $vneftps[] = $vneftp;
                }
            }
        }

        /* Set 'variantNameEntityForThePerson' as string or array */
        if(count($vneftps) > 0)
            $pld[$gfx.'variantNameEntityForThePerson'] = $this->blankNode($vneftps);


        $fv = $person->getCommentPerson();
        if($fv)
            $pld[$gfx.'biographicalOrHistoricalInformation'] = $this->xmlStringData($fv);

        $fv = $person->getDateBirth();
        if($fv) $pld[$gfx.'dateOfBirth'] = $this->xmlStringData($fv);

        $fv = $person->getDateDeath();
        if($fv) $pld[$gfx.'dateOfDeath'] = $this->xmlStringData($fv);

        // $fv = $person->getReligiousOrder();
        // if($fv) $pld['religiousOrder'] = $fv;


        $fv = $person->getGndid();
        if($fv) $pld[$gfx.'gndIdentifier'] = $this->xmlStringData($fv);

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
                $descOffices[] = $this->roleNode($oc, $roleNodeID);
                
                $diocese = $oc->getDiocese();
                $dioceseID = $this->getDioceseID($diocese);
                if($dioceseID) {
                    $descOffices[] = [
                        '@rdf:about' => $idpath.$dioceseID,
                        '#' => [
                            $scafx.'hasOccupation' => [
                                '@rdf:nodeID' => $roleNodeID
                            ]
                        ]
                    ];                    
                }
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

    public function roleNode($office, $roleNodeID) {
        $scafx = self::SCHEMA_PREFIX.':';
        $ocld['rdf:type'] = [
            '@rdf:resource' => self::NAMESP_SCHEMA.'Role'
        ];
        
        $ocld[$scafx.'roleName'] = $this->xmlStringData($office->getOfficeName());

        $fv = $office->getDateStart();
        if($fv) $ocld[$scafx.'startDate'] = $this->xmlStringData($fv);

        $fv = $office->getDateEnd();
        if($fv) $ocld[$scafx.'endDate'] = $this->xmlStringData($fv);

        $fv = $office->getDiocese();
        if($fv) $ocld[$scafx.'description'] = $this->xmlStringData($fv);

        $fv = $office->getMonastery();
        if($fv) $ocld[$scafx.'description'] = $this->xmlStringData($fv->getMonasteryName());
        
        $roleNode = [
            '@rdf:nodeID' => $roleNodeID,
            '#' => $ocld,
        ];                           

        return $roleNode;
    }


    public function xmlStringData($data) {
        return [
            '@rdf:datatype' => "http://www.w3.org/2001/XMLSchema#string",
            '#' => $data,
        ];
    }

    public function blankNode($data) {
        if(count($data) == 1)
            return $this->createBlankNode($data[0]);
        else
            return array_map([$this, 'createBlankNode'], $data);
    }

    public function createBlankNode($data) {
        $description = [
            'rdf:Description' => [
                '@rdf:nodeID' => uniqid("node"),
                '#' => $data,
            ]
        ];
        return $description;
    }


};
