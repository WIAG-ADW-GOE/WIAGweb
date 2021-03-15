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


    static public function xmlroot($data) {

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

    static public function xmlStringData($data) {
        return [
            '@rdf:datatype' => "http://www.w3.org/2001/XMLSchema#string",
            '#' => $data,
        ];
    }

    static public function rdfLangStringData($data, $lang) {
        return [
            '@rdf:datatype' => "http://www.w3.org/1999/02/22-rdf-syntax-ns#langString",
            '@xml:lang' => $lang,
            '#' => $data,
        ];
    }


    static public function blankNode($data) {
        if(count($data) == 1)
            return self::createBlankNode($data[0]);
        else
            return array_map('self::createBlankNode', $data);
    }

    static public function createBlankNode($data) {
        $description = [
            'rdf:Description' => [
                '@rdf:nodeID' => uniqid("node"),
                '#' => $data,
            ]
        ];
        return $description;
    }

};
