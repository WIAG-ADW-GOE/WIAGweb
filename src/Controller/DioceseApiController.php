<?php

namespace App\Controller;

use App\Entity\Diocese;
use App\Entity\Office;
use App\Form\Model\DioceseQueryFormModel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * handle API requests
 *
 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 */
class DioceseApiController extends AbstractController {

    /**
     * find diocese by ID; display details
     *
     * @see IDController::redirectID()
     *
     * @Route("/api/diocese/{wiagidlong}", name="api_diocese")
     */
    public function getdiocese($wiagidlong, Request $request) {

        $format = $request->query->get('format') ?? 'json';

        if(array_search($format, ['json', 'csv']) === false) {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        // remove prefix and suffix
        $id = Diocese::wiagidLongToId($wiagidlong);

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($id);
        # dd($diocese);

        if (!$diocese || $diocese === null) {
            throw $this->createNotFoundException('Diözese wurde nicht gefunden');
        }

        $dioceseExport = $diocese->toArray();
        switch($format) {
        case 'json':
            return $this->json(array('diocese' => $dioceseExport));
        case 'csv':
            $csvencoder = new CsvEncoder();
            $csvdata = $csvencoder->encode($dioceseExport, 'csv', [
                'csv_delimiter' => "\t",
            ]);
            $response =  new Response($csvdata);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$wiagidlong}.csv");
            return $response;
        }

    }


    /**
     * accept query request and deliver diocese data as JSON or CSV
     *
     * @Route("/api/query-dioceses", name="api_query_dioceses")
     */
    public function apigetdioceses(Request $request) {

        $format = $request->query->get('format') ?? 'json';

        if(array_search($format, ['json', 'csv']) === false) {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        $initialletter = $request->query->get('il');
        $name = $request->query->get('name');
        if($name) {
            $dioceses = $this->getDoctrine()
                             ->getRepository(Diocese::class)
                             ->findByNameWithBishopricSeat($name);
        }
        elseif($initialletter) {
            $dioceses = $this->getDoctrine()
                             ->getRepository(Diocese::class)
                             ->findByInitialLetterWithBishopricSeat($initialletter);
        }
        else {
            $dioceses = $this->getDoctrine()
                             ->getRepository(Diocese::class)
                             ->findByNameWithBishopricSeat("");
        }

        # dump($dioceses);

        $diocesesExport = array();
        foreach($dioceses as $d) {
            $diocesesExport[] = $d->toArray();
        }

        switch($format) {
        case 'json':
            return $this->json(array(
                'dioceses' => [
                    'count' => count($dioceses),
                    'list' => array_map(function($el) { return array('diocese' => $el); },
                                        $diocesesExport),
                ]
            ));
        case 'csv':
            $csvencoder = new CsvEncoder();
            $csvdata = $csvencoder->encode($diocesesExport, 'csv', [
                'csv_delimiter' => "\t",
            ]);
            $response =  new Response($csvdata);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename=WIAGDioceses.csv");
            return $response;
        }

        // return $this->render('start/welcome.html.twig');
    }

}
