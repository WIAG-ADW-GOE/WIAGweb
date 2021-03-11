<?php

namespace App\Service;

# use App\Entity\Diocese;
use App\Service\RDFData;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;


class DioceseLinkedData {

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

    public function dioceseToJSONLD($diocese, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $dioceseNode = $this->dioceseToJSONLinkedData($diocese, $baseurl);
        $jsondata = array_merge(self::JSONLDCONTEXT, $dioceseNode);
        $json = $serializer->serialize($jsondata, 'json');

        return $json;
    }

    public function diocesesToJSONLD($dioceses, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = self::JSONLDCONTEXT;
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseToJSONLinkedData($diocese, $baseurl));
        }

        $json = $serializer->serialize($dioceseNodes, 'json');

        return $json;
    }

    public function dioceseToJSONLinkedData($diocese, $baseurl) {
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
                $name = $altlabel['altLabel'];
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

    public function dioceseToRdf($person, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);
        $dioceseNode = $this->dioceseToLinkedData($person, $baseurl);

        $xmlroot = RDFData::xmlroot($dioceseNode);
        $rdf = $serializer->serialize($xmlroot, 'xml', RDFData::XML_CONTEXT);

        return $rdf;
    }

    public function diocesesToRdf($dioceses, $baseurl) {
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = array();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseToLinkedData($diocese, $baseurl));
        }
        $xmlroot = RDFData::xmlroot($dioceseNodes);
        $rdf = $serializer->serialize($xmlroot, 'xml', RDFData::XML_CONTEXT);

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
                $clabel[] = RDFData::rdfLangStringData($name, $lang);
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
