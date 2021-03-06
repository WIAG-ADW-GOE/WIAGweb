<?php

namespace App\Service;

use App\Entity\Diocese;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;


class PersonData {

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


    public function personToJSON($person, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $personNode = $this->personData($person, $baseurl);
        $json = $serializer->serialize($personNode, 'json');

        return $json;
    }

    public function personsToJSON($persons, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $personNodes = array();
        foreach($persons as $person) {
            array_push($personNodes, $this->personData($person, $baseurl));
        }

        $json = $serializer->serialize(['persons' => $personNodes], 'json');

        return $json;
    }

    public function personToCSV($person, $baseurl) {
        $personNode = $this->personData($person, $baseurl);

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($personNode, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function personsToCSV($persons, $baseurl) {
        $personNodes = array();
        foreach($persons as $person) {
            array_push($personNodes, $this->personData($person, $baseurl));
        }

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($personNodes, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function personsToXML($persons, $baseurl) {
        // see https://symfony.com/doc/current/components/serializer.html#the-xmlencoder-context-options
        $XML_CONTEXT = [
            'xml_format_output' => true,
            'xml_root_node_name' => 'WIAGBishops',
            'xml_encoding' => 'utf-8',
        ];
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $personNodes = array();
        foreach($persons as $person) {
            array_push($personNodes, $this->personData($person, $baseurl));
        }
        $rdf = $serializer->serialize($personNodes, 'xml', $XML_CONTEXT);

        return $rdf;
    }


    public function personData($person, $baseurl) {
        $pj = array();
        $pj['wiagId'] = $person->getWiagidLong();

        $fv = $person->getFamilyname();
        if($fv) $pj['familyName'] = $fv;

        $pj['givenName'] = $person->getGivenname();

        $fv = $person->getPrefixName();
        if($fv) $pj['prefix'] = $fv;

        $fv = $person->getFamilynameVariant();
        if($fv) $pj['familyNameVariant'] = $fv;

        $fv = $person->getGivennameVariant();
        if($fv) $pj['givenNameVariant'] = $fv;

        $fv = $person->getCommentName();
        if($fv) $pj['commentName'] = $fv;

        $fv = $person->getCommentPerson();
        if($fv) $pj['commentPerson'] = $fv;

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
        if($offices && count($offices) > 0) {
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

};
