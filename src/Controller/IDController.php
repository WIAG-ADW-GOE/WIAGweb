<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Diocese;
use App\Entity\Canon;
use App\Entity\CanonGS;
use App\Entity\CnOnline;
use App\Service\PersonData;
use App\Service\PersonLinkedData;
use App\Service\DioceseData;
use App\Service\DioceseLinkedData;
use App\Service\CanonService;
use App\Service\CanonData;
use App\Service\CanonLinkedData;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * provide single page data for bishops, canons, dioceses
 */
class IDController extends AbstractController {

    private $personData;
    private $personLinkedData;
    private $dioceseData;
    private $dioceseLinkedData;
    private $svccanon;
    private $svccanonData;
    private $svccanonLinkedData;
    //    private $svccanonGsData;
    //    private $svccanonGsLinkedData;


    const FORMAT_MAP = [
        'application/rdf+xml' => 'rdf',
        'application/ld+json' => 'jsonld',
        'text/csv' => 'csv',
        'application/json' => 'json',
        'text/html' => 'rdf', # standard within the route for data
    ];

    public function __construct(PersonData $personData,
                                PersonLinkedData $personLinkedData,
                                DioceseData $dioceseData,
                                DioceseLinkedData $dioceseLinkedData,
                                CanonService $svccanon,
                                CanonData $svccanonData,
                                CanonLinkedData $svccanonLinkedData) {
                                // CanonGSData $svccanonGsData,
                                // CanonGSLinkedData $svccanonGsLinkedData) {
        $this->personData = $personData;
        $this->personLinkedData = $personLinkedData;
        $this->dioceseData = $dioceseData;
        $this->dioceseLinkedData = $dioceseLinkedData;
        $this->svccanon = $svccanon;
        $this->svccanonData = $svccanonData;
        $this->svccanonLinkedData = $svccanonLinkedData;
        // $this->svccanonGsData = $svccanonGsData;
        // $this->svccanonGsLinkedData = $svccanonGsLinkedData;
    }

    /**
     * @Route("/id/{id}", name="id")
     */
    public function redirectID(string $id, Request $request) {
        $format = $request->query->get('format');
        if (!is_null($format) && $format != 'html')
            return $this->redirectToRoute('wiag_id_data', ['id' => $id, 'format' => $format], 303);
        else {
            // $format = $request->getPreferredFormat();
            $contentType = $request->getAcceptableContentTypes();
            if(!is_null($contentType) && $contentType[0] != 'text/html') {
                return $this->redirectToRoute('wiag_id_data', ['id' => $id], 303);
            }
            else
                // return $this->redirectToRoute('wiag_id_html', ['id' => $id], 303);
                return $this->routeDoc($id, $request);
        }
    }

    /**
     * @Route("/doc/{id}", name="wiag_id_html")
     */
    public function routeDoc(string $id, Request $request) {
        /* TODO check MIME type; default: HTML */
        if (Person::extractDbId($id)) {
            return $this->bishophtmlbyID($id);
        }
        elseif (Canon::isCanonId($id)) {
            return $this->canonhtmlbyID($id, $request);
        }
        elseif (Diocese::isIdDiocese($id)) {
            return $this->diocesehtmlbyID($id, $request);
        }
        else {
            throw $this->createNotFoundException('Keine Objekte für dieses ID-Muster');
        }
    }

    /**
     * @Route("/data/{id}", name="wiag_id_data")
     */
    public function routeData(string $id, Request $request) {
        if(Person::isIdBishop($id)) {
            return $this->bishopdata($id, $request);
        }
        elseif(Canon::isIdCanon($id)) {
            return $this->canondata($id, $request);
        }
        elseif(Diocese::isIdDiocese($id)) {
            return $this->diocesedata($id, $request);
        }
        else {
            throw $this->createNotFoundException('Keine Objekte für dieses ID-Muster');
        }
    }

    public function bishophtmlbyID(string $id) {
        $repository = $this->getDoctrine()
                           ->getRepository(Person::class);

        $person = $repository->findOneWithOffices($id);
        $repository->fillCnData($person);

        if (!$person) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }

        return $this->bishophtml($person);
    }

    public function bishophtml(?Person $person) {


        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $person->getWiagidLong(),
            'querystr' => null,
            'dioceserepository' => $dioceseRepository,
        ]);
    }

    public function bishopdata(string $id, Request $request) {
        $idbase = pathinfo($id, PATHINFO_FILENAME);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($idbase);

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
            $data = $this->personData->personToCSV($person, $baseurl);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$personID}.csv");
            break;
        case 'json':
            $data = $this->personData->personToJSON($person, $baseurl);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'jsonld':
        case 'json-ld':
            $data = $this->personLinkedData->personToJSONLD($person, $baseurl);
            $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
            break;
        case null:
        case 'rdf':
        case '':
            $data = $this->personLinkedData->personToRdf($person, $baseurl);
            $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format '.$format);
        }

        $response->setContent($data);

        return $response;
    }

    public function canonhtmlbyID(string $id) {

        $repo = $this->getDoctrine()
                     ->getRepository(CnOnline::class);

        $cnonline = $repo->findOneByWiagid($id);
        if (!$cnonline) {
            throw $this->createNotFoundException('Domherr wurde nicht gefunden');
        } else {
            $repo->fillData($cnonline);
            return $this->canonhtml($cnonline);
        }
    }

    public function canonhtml($canon) {
        # TODO do we need this here?
        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        // collect references
        $canon_dh = $canon->getCanonDh();
        $references = array();
        if (!is_null($canon_dh)) {
            $cycle = 1;
            $references = $this->svccanon->collectMerged($references, $canon_dh, $cycle);
            array_unshift($references, $canon_dh);
        }

        return $this->render('canon/details.html.twig', [
            'person' => $canon,
            'wiagidlong' => $canon->getId(),
            'querystr' => null,
            'dioceserepository' => $dioceseRepository,
            'references' => $references,
        ]);
    }

    public function canondata(string $id, Request $request) {
        $idbase = pathinfo($id, PATHINFO_FILENAME);

        $canononline = $this->getDoctrine()
                            ->getRepository(CnOnline::class)
                            ->findOneById($idbase);
        # dd($idbase, $canononline);

        $canon = null;
        if ($canononline->getIdDh()) {
            $canon = $this->getDoctrine()
                          ->getRepository(Canon::class)
                          ->findOneWithOffices($idbase);
        } elseif ($canononline->getIdGs()) {
            $canon = $this->getDoctrine()
                          ->getRepository(CanonGS::class)
                          ->findOneWithOffices($idbase);
        }
        $svcData = $this->svccanonData;
        $svcLinkedData = $this->svccanonLinkedData;


        if(!$canon) {
            throw $this->createNotFoundException('Domherr wurde nicht gefunden');
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
            $canonID = $canon->getWiagidLong();
            $data = $svcData->canonToCSV($canon, $baseurl);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$canonID}.csv");
            break;
        case 'json':
            $data = $svcData->canonToJSON($canon, $baseurl);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'jsonld':
        case 'json-ld':
            $data = $svcLinkedData->canonToJSONLD($canon, $baseurl);
            $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
            break;
        case null:
        case 'rdf':
        case '':
            $data = $svcLinkedData->canonToRdf($canon, $baseurl);
            $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format '.$format);
        }

        $response->setContent($data);

        return $response;
    }

    public function diocesehtmlbyID(string $id, Request $request) {

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($id);

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden: {$id}");
        }

        return $this->diocesehtml($diocese);
    }

    public function diocesehtml($diocese) {
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
            throw $this->createNotFoundException('Bistum wurde nicht gefunden');
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
            $data = $this->dioceseData->dioceseToCSV($diocese, $baseurl);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$dioceseID}.csv");
            break;
        case 'json':
            $data = $this->dioceseData->dioceseToJSON($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'json-ld':
        case 'jsonld':
            $data = $this->dioceseLinkedData->dioceseToJSONLD($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
            break;
        case null:
        case '':
        case 'rdf':
            $data = $this->dioceseLinkedData->dioceseToRdf($diocese, $baseurl);
            $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
            break;
        default:
            throw $this->createNotFoundException('Unbekanntes Format');
        }

        $response->setContent($data);

        return $response;
    }

    /**
     * @Route("/gnd/{id}", name="gnd_id")
     */
    public function detailsByGndId(string $id) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneByGndid($id);

        if ($person) {
            return $this->bishophtml($person);
        }

        $canon = $this->getDoctrine()
                      ->getRepository(Canon::class)
                      ->findOneByGndId($id);

        if ($canon) {
            return $this->canonhtml($canon);
        }

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findOneByGndId($id);

        if ($diocese) {
            return $this->diocesehtml($diocese);
        }

        throw $this->createNotFoundException('GND-ID wurde nicht gefunden');

    }

}
