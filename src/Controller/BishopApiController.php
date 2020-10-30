<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;

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
 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 */
class BishopApiController extends AbstractController {

    /**
     * @Route("/api/bishop/{wiagidlong}", name="api_bishop")
     */
    public function getperson($wiagidlong, Request $request) {

        $format = $request->query->get('format') ?? 'json';
        
        if(array_search($format, ['json', 'csv']) === false) {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        // remove prefix and suffix
        $id = Person::wiagidLongToWiagid($wiagidlong);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($id);

        if (!$person || $person === null) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }
        
        $personExport = $person->toArray();
        switch($format) {
        case 'json':
            return $this->json($personExport);
        case 'csv':
            $csvencoder = new CsvEncoder();
            $csvdata = $csvencoder->encode($personExport, 'csv', [
                'csv_delimiter' => "\t",
            ]);
            $response =  new Response($csvdata);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename={$wiagidlong}.csv");
            return $response;
        }
            


    }


    /**
     * @Route("/api/query-bishops", name="api_query_bishops")
     */
    public function apigetpersons(Request $request) {

        $format = $request->query->get('format') ?? 'json';
        
        if(array_search($format, ['json', 'csv']) === false) {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }


        $name = $request->query->get('name');
        $place = $request->query->get('diocese');
        $office = $request->query->get('office');
        $year = $request->query->get('year');
        $someid = $request->query->get('someid');

        if($someid && Person::isWiagidLong($someid)) {
            $someid = Person::wiagidLongToWiagid($someid);
        }

        $bishopquery = new BishopQueryFormModel($name,
                                                $place,
                                                $office,
                                                $year,
                                                $someid,
                                                array(),
                                                array());

        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findWithOffices($bishopquery);

        # dump($persons);

        $personsExport = array();
        foreach($persons as $p) {
            $personsExport[] = $p->toArray();
        }

        switch($format) {
        case 'json':
            return $this->json(array(
                'persons' => [
                    'count' => count($persons),
                    'list' => $personsExport,
                ]
            ));            
        case 'csv':
            $csvencoder = new CsvEncoder();
            $csvdata = $csvencoder->encode($personsExport, 'csv', [
                'csv_delimiter' => "\t",
            ]);
            $response =  new Response($csvdata);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename=WIAGResult.csv");
            return $response;
        }

        // return $this->render('start/welcome.html.twig');
    }

}
