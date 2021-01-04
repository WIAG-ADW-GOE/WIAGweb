<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Diocese;
use App\Service\CSVData;
use App\Service\JSONData;
use App\Service\RDFData;
use App\Service\JSONLDData;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;



class IDController extends AbstractController {

    private $csvdata;
    private $jdata;
    private $rdfdata;
    private $jlddata;

    const FORMAT_MAP = [
        'application/rdf+xml' => 'rdf',
        'application/ld+json' => 'jsonld',
        'text/csv' => 'csv',
        'application/json' => 'json',
        'text/html' => 'rdf', # standard within the route for data
    ];

    public function __construct(CSVData $csvdata, JSONData $jdata, RDFData $rdfdata, JSONLDData $jlddata) {
        $this->csvdata = $csvdata;
        $this->jdata = $jdata;
        $this->rdfdata = $rdfdata ;
        $this->jlddata = $jlddata;
    }

    /**
     * @Route("/id/{id}", name="id")
     */
    public function redirectID(string $id, Request $request) {
        $format = $request->query->get('format');
        if(!is_null($format))
            return $this->redirectToRoute('wiag_id_data', ['id' => $id, 'format' => $format], 303);
        else {
            // $format = $request->getPreferredFormat();
            $contentType = $request->getAcceptableContentTypes();
            if(!is_null($contentType) && $contentType[0] != 'text/html') {
                return $this->redirectToRoute('wiag_id_data', ['id' => $id], 303);
            }
            else
                return $this->redirectToRoute('wiag_id_html', ['id' => $id], 303);
        }
    }

    /**
     * @Route("/doc/{id}", name="wiag_id_html")
     */
    public function routeDoc(string $id, Request $request) {
        /* TODO check MIME type; default: HTML */
        if(preg_match("/WIAG-Pers-EPISCGatz-.+/", $id))
            return $this->bishophtml($id, $request);
        elseif(preg_match("/WIAG-Inst-DIOCGatz-.+/", $id))
            return $this->diocesehtml($id, $request);
        else
            throw $this->createNotFoundException('Keine Objekte für dieses ID-Muster');
    }

    /**
     * @Route("/data/{id}", name="wiag_id_data")
     */
    public function routeData(string $id, Request $request) {
        if(preg_match("/WIAG-Pers-EPISCGatz-.+/", $id))
            return $this->bishopdata($id, $request);
        elseif(preg_match("/WIAG-Inst-DIOCGatz-.+/", $id))
            return $this->diocesedata($id, $request);
        else
            throw $this->createNotFoundException('Keine Objekte für dieses ID-Muster');
    }

    public function bishophtml(string $id, Request $request) {

        $idindb = Person::wiagidLongToWiagid($id);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($idindb);

        if (!$person) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $id,
            'querystr' => null,
            'dioceserepository' => $dioceseRepository,
        ]);
    }

    public function bishopdata(string $id, Request $request) {
        $idbase = pathinfo($id, PATHINFO_FILENAME);
        $idindb = Person::wiagidLongToWiagid($idbase);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($idindb);

        if(!$person) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $response = new Response();
        $baseurl = $request->getSchemeAndHttpHost();
        $extension = pathinfo($id, PATHINFO_EXTENSION);
        $format = $request->query->get('format');
        if(is_null($format)) {
            $contentType = $request->getAcceptableContentTypes();
            if(!is_null($contentType)) {
                $key = $contentType[0];
                if(!array_key_exists($key, self::FORMAT_MAP))
                    throw $this->createNotFoundException('Unbekanntes Format '.$key);
                $format = self::FORMAT_MAP[$key];
            }
        }

        switch($format) {
        case 'csv':
            $personID = $person->getWiagidLong();
            $data = $this->csvdata->personToCSV($person, $baseurl);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$personID}.csv");
            break;
        case 'json':
            $data = $this->jdata->personToJSON($person, $baseurl);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'jsonld':
        case 'json-ld':
            $data = $this->jlddata->personToJSONLD($person, $baseurl);
            $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
            break;
        case null:
        case 'rdf':
        case '':
            $data = $this->rdfdata->personToRdf($person, $baseurl);
            $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format '.$format);
        }

        $response->setContent($data);

        return $response;
    }

    public function diocesehtml(string $id, Request $request) {

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($id);

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden: {$id}");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
        ]);
    }

    public function diocesedata(string $id, Request $request) {

        $idbase = pathinfo($id, PATHINFO_FILENAME);

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($idbase);

        if(!$diocese) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $response = new Response();
        $baseurl = $request->getSchemeAndHttpHost();
        $format = $request->query->get('format');
        if(is_null($format)) {
            $contentType = $request->getAcceptableContentTypes();
            if(!is_null($contentType)) {
                $key = $contentType[0];
                if(!array_key_exists($key, self::FORMAT_MAP))
                    throw $this->createNotFoundException('Unbekanntes Format '.$key);
                $format = self::FORMAT_MAP[$key];
            }
        }

        switch($format) {
        case 'csv':
            $dioceseID = $diocese->getWiagidLong();
            $data = $this->csvdata->dioceseToCSV($diocese, $baseurl);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$dioceseID}.csv");
            break;
        case 'json':
            $data = $this->jdata->dioceseToJSON($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'json-ld':
        case 'jsonld':
            $data = $this->jlddata->dioceseToJSONLD($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
            break;
        case null:
        case '':
        case 'rdf':
            $data = $this->rdfdata->dioceseToRdf($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format');
        }

        $response->setContent($data);

        return $response;
    }



}
