<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;
use App\Service\PersonData;

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
class BishopApiController extends AbstractController {

    /**
     * find bishop by ID; deliver data as JSON or CSV
     *
     * 2021-07-15 obsolete
     *
     * @see IDController
     *
     * @Route("/api/bishop/{wiagidlong}", name="api_bishop")
     */
    public function getperson($wiagidlong, Request $request, PersonData $persondata) {

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

        $personExport = $personData->personToJSON($person);
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
     * accept query request and deliver bishop data as JSON or CSV
     *
     * @Route("/api/query-bishops", name="api_query_bishops")
     */
    public function apigetpersons(Request $request, PersonData $personData) {

        $query = $request->query;
        $format = $query->get('format') ?? 'json';

        if(array_search($format, ['json', 'csv']) === false) {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }



        $name = $query->get('name');
        $place = $query->get('diocese');
        $office = $query->get('office');
        $year = $query->get('year');
        $someid = $query->get('someid');

        $dbId = Person::extractDbId($someid) ?? $someid;

        $bishopquery = new BishopQueryFormModel($name,
                                                $place,
                                                $office,
                                                $year,
                                                $dbId,
                                                array(),
                                                array());

        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findWithOffices($bishopquery);


        $response = new Response();
        switch($format) {
        case 'json':
            $data = $personData->personsToJSON($persons, null);
            $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
            break;
        case 'csv':
            $data = $personData->personsToCSV($persons, null);
            $response->headers->set('Content-Type', "text/csv; charset=utf-8");
            $response->headers->set('Content-Disposition', "filename=WIAGBishops.csv");
            break;
        }

        $response->setContent($data);

        return $response;
    }

}
