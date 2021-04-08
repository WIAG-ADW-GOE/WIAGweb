<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Diocese;
use App\Service\PersonData;
use App\Service\PersonLinkedData;
use App\Service\DioceseData;
use App\Service\DioceseLinkedData;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_DATA_ADMIN")
 */
class ExportController extends AbstractController {

    private $personData;
    private $personLinkedData;
    private $dioceseData;
    private $dioceseLinkedData;

    const FORMAT_MAP = [
        'application/rdf+xml' => 'rdf',
        'application/ld+json' => 'jsonld',
        'text/csv' => 'csv',
        'application/json' => 'json',
        'text/html' => 'rdf', # standard within the route for data
    ];

    public function __construct(PersonData $personData, PersonLinkedData $personLinkedData, DioceseData $dioceseData, DioceseLinkedData $dioceseLinkedData) {
        $this->personData = $personData;
        $this->personLinkedData = $personLinkedData;
        $this->dioceseData = $dioceseData;
        $this->dioceseLinkedData = $dioceseLinkedData;
    }

    /**
     * @Route("/bulk/export", name="bulk_export_dispatch")
     */
    public function dispatchexport (Request $request) {
        return $this->render('admin/data_export.html.twig');
    }

    /**
     * @Route("/bulk/export/bishop/{format}", name="bulk_bishop_export")
     */
    public function personsData(string $format, Request $request) {
        $test = false;
        if ($test) {
            $limit = 100;
            $offset = 1344;
        } else {
            $limit = 0;
            $offset = 0;
        }

        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findWithOffices(null, $limit, $offset);

        $response = new Response();
        $baseurl = $request->getSchemeAndHttpHost();
        $mimetype = "text/plain";
        $filename = "";
        ini_set("memory_limit", "265M");

        switch($format) {
        case 'csv':
            $data = $this->personData->personsToCSV($persons, $baseurl);
            $mimeType = 'text/csv';
            $filename = "WIAG-bishops.csv";
            break;
        case 'json':
            $data = $this->personData->personsToJSON($persons, $baseurl);
            $mimeType = 'text/plain';
            $filename = "WIAG-bishops.json";
            break;
        case 'xml':
            $data = $this->personData->personsToXML($persons, $baseurl);
            $mimeType = 'text/xml';
            $filename = "WIAG-bishops.xml";
            // $zipfile = '/tmp'.'/WIAG-bishops'.'.gz';
            // $zh = gzopen($zipfile, 'w9');
            // gzwrite($zh, $data);
            // gzclose($zh);
            break;
        case 'jsonld':
        case 'json-ld':
            $data = $this->personLinkedData->personsToJSONLD($persons, $baseurl);
            $mimeType = 'text/plain';
            $filename = "WIAG-bishops-ld.json";
            break;
        case null:
        case 'rdf':
        case '':
            $data = $this->personLinkedData->personsToRdf($persons, $baseurl);
            $mimeType = 'text/xml';
            $filename = "WIAG-bishops-ld.xml";
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format '.$format);
        }

        $response->headers->set('Content-Type', $mimeType.'; charset=UTF-8');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);

        $response->setContent($data);
        ini_restore("memory_limit");

        return $response;
    }

    /**
     * @Route("/bulk/export/diocese/{format}", name="bulk_diocese_export")
     */
    public function diocesesdata(string $format, Request $request) {
        $dioceses = $this->getDoctrine()
                         ->getRepository(Diocese::class)
                         ->findByNameWithBishopricSeat();

        if(!$dioceses) {
            throw $this->createNotFoundException('Bistümer wurden nicht gefunden');
        }

        $response = new Response();
        $baseurl = $request->getSchemeAndHttpHost();
        $mimetype = "text/plain";
        $filename = "";

        switch($format) {
        case 'csv':
            $data = $this->dioceseData->diocesesToCSV($dioceses, $baseurl);
            $mimeType = 'text/csv';
            $filename = "WIAG-dioceses.csv";
            break;
        case 'json':
            $data = $this->dioceseData->diocesesToJSON($dioceses, $baseurl);
            $mimeType = 'text/plain';
            $filename = "WIAG-dioceses.json";
            break;
        case 'xml':
            $data = $this->dioceseData->diocesesToXML($dioceses, $baseurl);
            $mimeType = 'text/xml';
            $filename = "WIAG-dioceses.xml";
            break;
        case 'json-ld':
        case 'jsonld':
            $data = $this->dioceseLinkedData->diocesesToJSONLD($dioceses, $baseurl);
            $mimeType = 'text/plain';
            $filename = "WIAG-dioceses-ld.json";
            break;
        case null:
        case '':
        case 'rdf':
            $data = $this->dioceseLinkedData->diocesesToRdf($dioceses, $baseurl);
            $mimeType = 'text/xml';
            $filename = "WIAG-dioceses-ld.xml";
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format');
        }

        $response->headers->set('Content-Type', $mimeType.'; charset=UTF-8');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $disposition);

        $response->setContent($data);

        return $response;
    }

    /**
     * @Route("/beacon.txt", name="beacon")
     * Return list of GND numbers for all bishops
     */
    public function beacon (Request $request) {
        $response = new Response();
        $data = "line1\nline2";
        $mimeType = 'text/plain';
        $filename = "beacon.txt";
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Type', $mimeType.'; charset=UTF-8');
        $response->headers->set('Content-Disposition', $disposition);
        $baseurl = $request->getSchemeAndHttpHost();
        $cbeaconheader = [
            "#FORMAT: BEACON",
            "#VERSION: 0.1",
            "#PREFIX: http://d-nb.info/gnd/",
            "#TARGET: ".$baseurl."/gnd/{ID}",
            "#FEED: ".$baseurl."/beacon.txt",
            "#NAME: Wissensaggregator Mittelalter und Frühe Neuzeit",
            "#DESCRIPTION: ",
            "#INSTITUTION: Germania Sacra, Akademie der Wissenschaften zu Goettingen",
            "#CONTACT: bkroege@gwdg.de",
            "#TIMESTAMP: ".date(DATE_ATOM),
        ];

        $gnds = $this->getDoctrine()
                      ->getRepository(Person::class)
                      ->findAllGnds();


        $cdata = array_merge($cbeaconheader, array_column($gnds, 'gndid'));

        $data = implode($cdata, "\n");

        $response->setContent($data);
        return $response;
    }


}
