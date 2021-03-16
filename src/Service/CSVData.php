<?php

namespace App\Service;

use App\Entity\Diocese;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;


class CSVData {

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

    
    public function personData($person, $baseurl): array {
        $wiagid = $person->getWiagidLong();
        
        $pj = array();
        
        $pj['wiagId'] = $wiagid;

        $idpath = $baseurl.'/'.self::ID_PATH;

        $pj['URI'] = $idpath.$wiagid;

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
                // dump($ocJSON);
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


    /**
     * Get ID of `diocese` from the database.
     */
    public function getDioceseID(string $diocese): string {
        if(is_null($diocese)) return null;
        $diocRepository = $this->entitymanager->getRepository(Diocese::class);
        $diocObj = $diocRepository->findByNameWithBishopricSeat($diocese);
        $diocID = null;
        if($diocObj) {
            $diocID = $diocObj[0]->getWiagIdLong();

        }
        return $diocID;
    }

    public function roleNode($office) {
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
            if($dioceseID) $ocld[$gndfx.'affiliation'] = $dioceseID;
        }


        $fv = $office->getMonastery();
        if($fv) $ocld[$scafx.'description'] = $fv->getMonasteryName();

        return $ocld;
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

    public function dioceseData($diocese, $baseurl) {
        $cd = array();
        $wiagid = $diocese->getWiagidLong();
        $cd['wiagid'] = $wiagid;

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
                    $jsonName = "wikipediaUrl";
                    $baseurl = $extid->getAuthority()->getUrlFormatter();
                    $extidurl = $baseurl.urlencode($extidurl);
                }
                $cei[$jsonName] = $extidurl;
            }
            $cd['identifiers'] = $cei;
        }

        $fv = $diocese->getCommentAuthorityFile();
        if($fv) $cd['identifiersComment'] = $fv;

        return $cd;
    }

};
