<?php

namespace App\Service;

use App\Entity\Diocese;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;


class DioceseData {

    const ID_PATH = 'id/';

    const GS_URL="http://personendatenbank.germania-sacra.de/index/gsn/";
    const GND_URL="http://d-nb.info/gnd/";
    const WIKIDATA_URL="https://www.wikidata.org/wiki/";
    const VIAF_URL="https://viaf.org/viaf/";

    const NAMESP_GND="https://d-nb.info/standards/elementset/gnd#";
    const NAMESP_SCHEMA="https://schema.org/";

    const EXTERNAL_ID_FIELDS = [
        'Wikidata' => 'wikidataId',
        'Factgrid' => 'factgridId',
        'Gemeinsame Normdatei (GND) ID' => 'gndId',
        'VIAF-ID' => 'viafId',
        'CERL-ID' => 'cerlId',
        'Catholic Hierarchy, Diocese' => 'catholicHierarchyDiocese',
        'Wikipedia-Artikel' => 'wikipediaUrl',
    ];

    public function dioceseToJSON($diocese, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $dioceseNode = $this->dioceseData($diocese, $baseurl);
        $json = $serializer->serialize($dioceseNode, 'json');

        return $json;
    }

    public function diocesesToJSON($dioceses, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = array();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseData($diocese, $baseurl));
        }

        $json = $serializer->serialize(['dioceses' => $dioceseNodes], 'json');

        return $json;
    }

    public function dioceseToCSV($diocese, $baseurl) {
        $dioceseNode = $this->dioceseData($diocese, $baseurl);

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($dioceseNode, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function diocesesToCSV($dioceses, $baseurl) {
        $dioceseNodes = array();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseData($diocese, $baseurl));
        }

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($dioceseNodes, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function diocesesToXML($dioceses, $baseurl) {
        // see https://symfony.com/doc/current/components/serializer.html#the-xmlencoder-context-options
        $XML_CONTEXT = [
            'xml_format_output' => true,
            'xml_root_node_name' => 'WIAGDioceses',
            'xml_encoding' => 'utf-8',
        ];
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $dioceseNodes = array();
        foreach($dioceses as $diocese) {
            array_push($dioceseNodes, $this->dioceseData($diocese, $baseurl));
        }
        $rdf = $serializer->serialize($dioceseNodes, 'xml', $XML_CONTEXT);

        return $rdf;
    }

    public function dioceseData($diocese, $baseurl) {
        $cd = array();
        $wiagid = $diocese->getWiagidLong();
        $cd['wiagId'] = $wiagid;

        $idpath = $baseurl.'/'.self::ID_PATH;

        $cd['URI'] = $idpath.$wiagid;

        $fv = $diocese->getDiocese();
        if($fv) $cd['name'] = $fv;

        $fv = $diocese->getDioceseStatus();
        if($fv) $cd['status'] = $fv;

        $fv = $diocese->getDateOfFounding();
        if($fv) $cd['dateOfFounding'] = $fv;

        $fv = $diocese->getDateOfDissolution();
        if($fv) {
            $cd['dateOfDissolution']
                = $fv == 'keine' ? 'none' : $fv;
        }

        $fv = $diocese->getAltlabel();
        if($fv) {
            $clabel = array();
            foreach($fv as $label) {
                $clabel[] = $label->toArray();
            }
            $cd['altLabels'] = $clabel;
        }

        $fv = $diocese->getNoteDiocese();
        if($fv) $cd['note'] = $fv;

        $fv = $diocese->getEcclesiasticalProvince();
        if($fv) $cd['ecclesiasticalProvince'] = $fv;

        $fv = $diocese->getBishopricseatobj();
        if($fv) $cd['bishopricSeat'] = $fv->getPlaceName();

        $fv = $diocese->getNoteBishopricSeat();
        if($fv) $cd['noteBishopricSeat'] = $fv;

        $fv = $diocese->getExternalUrls();
        if($fv) {
            $cei = array();
            foreach($fv as $extid) {
                $jsonName = $extid->getAuthority()->getUrlNameFormatter();
                $extidurl = $extid->getUrlValue();
                if($jsonName == "Wikipedia-Artikel") {
                    $baseurl = $extid->getAuthority()->getUrlFormatter();
                    $extidurl = $baseurl.urlencode($extidurl);
                }
                $extidfieldname = self::EXTERNAL_ID_FIELDS[$jsonName];
                $cei[$extidfieldname] = $extidurl;
            }
            $cd['identifiers'] = $cei;
        }

        $fv = $diocese->getReference();
        if($fv) {
            $cd['reference'] = $fv->toArray();
            $fiv = $diocese->getGatzPages();
            if($fiv)
                $cd['reference']['pages'] = $fiv;
        }

        $fv = $diocese->getCommentAuthorityFile();
        if($fv) $cd['identifiersComment'] = $fv;

        return $cd;
    }

};
