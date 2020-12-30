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
            '@xmlns:owl' => "http://www.w3.org/2002/07/owl#",
            '@xmlns:schema' => "http://schema.org/",
            '@xmlns:gndo' => self::NAMESP_GND,
            '@xmlns:foaf' => "http://xmlns.com/foaf/0.1/",
            '#' => [
                'rdf:Description' => $data
                ]
        ];
        return $node;
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

    public function roleNode($office, $roleNodeID, $idpath) {
        $scafx = "schema:";
        $gfx = "gndo:";

        $ocld['rdf:type'] = [
            '@rdf:resource' => self::NAMESP_SCHEMA.'Role'
        ];

        $ocld[$scafx.'roleName'] = $this->xmlStringData($office->getOfficeName());

        $fv = $office->getDateStart();
        if($fv) $ocld[$scafx.'startDate'] = $this->xmlStringData($fv);

        $fv = $office->getDateEnd();
        if($fv) $ocld[$scafx.'endDate'] = $this->xmlStringData($fv);

        $fv = $office->getDiocese();
        if($fv) {
            $dioceseID = $this->getDioceseID($fv);
            if($dioceseID)
                $ocld[$gfx.'affiliation'] = [
                    '@rdf:resource' => $idpath.$dioceseID
                ];
            $ocld[$scafx.'description'] = $this->xmlStringData($fv);
        }

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

    public function rdfLangStringData($data, $lang) {
        return [
            '@rdf:datatype' => "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString",
            '@xml:lang' => $lang,
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

    public function dioceseToRdf($person, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);
        $dioceseNode = $this->dioceseToLinkedData($person, $baseurl);

        $xmlroot = $this->xmlroot($dioceseNode);
        $rdf = $serializer->serialize($xmlroot, 'xml', self::XML_CONTEXT);

        return $rdf;
    }

    public function diocesesToRdf($dioceses, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = array();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseToLinkedData($diocese, $baseurl));
        }
        $xmlroot = $this->xmlroot($dioceseNodes);
        $rdf = $serializer->serialize($xmlroot, 'xml', self::XML_CONTEXT);

        return $rdf;
    }


    public function dioceseToLinkedData($diocese, $baseurl): array {
        $dld = array();

        $gfx = "gndo:";
        $owlfx = "owl:";
        $foaffx = "foaf:";
        $scafx = "schema:";

        $idpath = $baseurl.'/'.self::ID_PATH;

        $dld = [
            'rdf:type' => [
                '@rdf:resource' => self::NAMESP_GND.'ReligiousAdministrativeUnit'
                ]
        ];

        $dioceseID = $diocese->getWiagidLong();


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
                $clabel[] = $this->rdfLangStringData($name, $lang);
            }
            $dld[$gfx.'variantName'] = $clabel;
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

        // $fv = $diocese->getEcclesiasticalProvince();
        // if($fv) $dld['ecclesiasticalProvince'] = $fv;

        // $fv = $diocese->getBishopricseatobj();
        // if($fv) $dld['bishopricSeat'] = $fv->getPlaceName();


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

        // $fv = $diocese->getCommentAuthorityFile();
        // if($fv) $dld['identifiersComment'] = $fv;

        $descName = [
            '@rdf:about' => $idpath.$dioceseID,
            '#' => $dld,
        ];

        return $descName;

    }


};
