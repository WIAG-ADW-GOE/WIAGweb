<?php

namespace App\Service;

use App\Entity\Diocese;
use App\Service\CnOfficeData;
use App\Service\CnReferenceData;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;


class CanonData {

    const ID_PATH = 'id/';

    const GS_URL="http://personendatenbank.germania-sacra.de/index/gsn/";
    const GND_URL="http://d-nb.info/gnd/";
    const WIKIDATA_URL="https://www.wikidata.org/wiki/";
    const VIAF_URL="https://viaf.org/viaf/";

    const NAMESP_GND="https://d-nb.info/standards/elementset/gnd#";
    const NAMESP_SCHEMA="https://schema.org/";


    private $entitymanager;
    private $officeData;
    private $referenceData;

    public function __construct(EntityManagerInterface $em,
                                CnOfficeData $officeData,
                                CnReferenceData $referenceData) {
        $this->entitymanager = $em;
        $this->officeData = $officeData;
        $this->referenceData = $referenceData;
    }


    public function canonToJSON($canon, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);
        $canonNode = $this->canonData($canon, $baseurl);
        $json = $serializer->serialize($canonNode, 'json');

        return $json;
    }

    public function canonsToJSON($canons, $baseurl) {
        $encoders = array(new JsonEncoder());
        $serializer = new Serializer([], $encoders);

        $canonNodes = array();
        foreach($canons as $canon) {
            array_push($canonNodes, $this->canonData($canon, $baseurl));
        }

        $json = $serializer->serialize(['canons' => $canonNodes], 'json');

        return $json;
    }

    public function canonToCSV($canon, $baseurl) {
        $canonNode = $this->canonData($canon, $baseurl);

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($canonNode, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function canonsToCSV($canons, $baseurl) {
        $canonNodes = array();
        foreach($canons as $canon) {
            array_push($canonNodes, $this->canonData($canon, $baseurl));
        }

        $csvencoder = new CsvEncoder();
        $csv = $csvencoder->encode($canonNodes, 'csv', [
            'csv_delimiter' => "\t",
        ]);

        return $csv;
    }

    public function canonsToXML($canons, $baseurl) {
        // see https://symfony.com/doc/current/components/serializer.html#the-xmlencoder-context-options
        $XML_CONTEXT = [
            'xml_format_output' => true,
            'xml_root_node_name' => 'WIAGBishops',
            'xml_encoding' => 'utf-8',
        ];
        $encoders = array(new XmlEncoder());
        $serializer = new Serializer([], $encoders);

        $canonNodes = array();
        foreach($canons as $canon) {
            array_push($canonNodes, $this->canonData($canon, $baseurl));
        }
        $rdf = $serializer->serialize($canonNodes, 'xml', $XML_CONTEXT);

        return $rdf;
    }


    public function canonData($canon, $baseurl) {
        $pj = array();
        $pj['id'] = $canon->getId();

        $fv = $canon->getFamilyname();
        if($fv) $pj['familyName'] = $fv;

        $pj['givenName'] = $canon->getGivenname();

        $fv = $canon->getPrefixName();
        if($fv) $pj['prefix'] = $fv;

        $fv = $canon->getFamilynameVariant();
        if($fv) $pj['familyNameVariant'] = $fv;

        $fv = $canon->getGivennameVariant();
        if($fv) $pj['givenNameVariant'] = $fv;

        $fv = $canon->getAcademicTitle();
        if ($fv) $pj['academicTitle'] = $fv;

        $fv = $canon->getCommentName();
        if($fv) $pj['commentName'] = $fv;

        $fv = $canon->getCommentPerson();
        if($fv) $pj['commentPerson'] = $fv;

        $fv = $canon->getDateBirth();
        if($fv) $pj['dateOfBirth'] = $fv;

        $fv = $canon->getDateDeath();
        if($fv) $pj['dateOfDeath'] = $fv;

        if($canon->hasExternalIdentifier() || $canon->hasOtherIdentifier()) {
            $pj['identifier'] = array();
            $nd = &$pj['identifier'];
            $fv = $canon->getGsnId();
            if($fv) $nd['gsId'] = $fv;

            $fv = $canon->getGndId();
            if($fv) $nd['gndId'] = $fv;

            $fv = $canon->getViafid();
            if($fv) $nd['viafId'] = $fv;

            $fv = $canon->getWikidataId();
            if($fv) $nd['wikidataId'] = $fv;

            $fv = $canon->getWikipediaUrl();
            if($fv) $nd['wikipediaUrl'] = $fv;
        }

        $offices = $canon->getOffices();
        if($offices && count($offices) > 0) {
            $pj['offices'] = array();
            $ocJSON = &$pj['offices'];
            foreach($offices as $oc) {
                $ocJSON[] = CnOfficeData::toArray($oc);
            }
        }

        $fv = $canon->getReferences();
        if($fv) {
            $pj['reference'] = array();
            foreach($fv as $ref) {
                $refv = CnReferenceData::toArray($ref->getReference());
                $fiv = $ref->getPageReference();
                if($fiv) {
                    $nodereference['pages'] = $fiv;
                }
                $pj['reference'][] = $refv;
            }
        }

        return $pj;

    }

};
