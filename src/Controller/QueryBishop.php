<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;
use App\Entity\Canon;
use App\Entity\CnOnline;
use App\Entity\CnCanonReferenceGS;
use App\Repository\PersonRepository;
use App\Service\PersonData;
use App\Service\PersonLinkedData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * query bishops
 */
class QueryBishop extends AbstractController {
    /** number of items per page */
    const PAGE_SIZE = 20;

    /**
     * display query form for bishops; handle query
     *
     * @Route("/bischoefe", name="launch_query")
     */
    public function launch_query(Request $request,
                                 PersonRepository $personRepository,
                                 PersonData $personData,
                                 PersonLinkedData $personLinkedData) {

        // we need to pass an instance of BishopQueryFormModel, because facets depend on it's data
        $bishopquery = new BishopQueryFormModel;

        $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

        $form->handlerequest($request);

        // $facetPlacesState = 'hide';
        // $facetOfficesState = 'hide';
        $facetPlacesState = 'show';
        $facetOfficesState = 'show';

        if ($form->isSubmitted() && $form->isValid()) {
            $bishopquery = $form->getData();

            # strip 'Bistum' or 'Erzbistum'
            $bishopquery->normPlace();

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getBishopInQuery($form, $singleoffset);
            }


            // get the number of results (without page size restriction)
            $count = $this->getDoctrine()
                          ->getRepository(Person::class)
                          ->countByQueryObject($bishopquery)[1];


            $offset = 0;
            $querystr = null;
            $persons = null;

            if($count > 0 && $form->getClickedButton()) {

                $buttonname = $form->getClickedButton()->getName();
                if($buttonname != 'searchHTML') {
                    $persons = $personRepository->findWithOffices($bishopquery);
                    $baseurl = $request->getSchemeAndHttpHost();
                    $response = new Response();

                    switch($buttonname) {
                    case 'searchJSON':
                        $data = $personData->personsToJSON($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
                        break;
                    case 'searchCSV':
                        $data = $personData->personsToCSV($persons, $baseurl);
                        $response->headers->set('Content-Type', "text/csv; charset=utf-8");
                        $response->headers->set('Content-Disposition', "filename=WIAG-Pers-EPISCGatz.csv");
                        break;
                    case 'searchRDF':
                        $data = $personLinkedData->personsToRdf($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
                        break;
                    case 'searchJSONLD':
                        $data = $personLinkedData->personsToJSONLD($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
                    }
                    $response->setContent($data);
                    return $response;
                }
            }

            // return HTML

            $offset = $request->request->get('offset') ?? 0;


            // extra check to avoid empty lists
            if($count < self::PAGE_SIZE) $offset = 0;

            $offset = (int) floor($offset / self::PAGE_SIZE) * self::PAGE_SIZE;
            $addMonasteryLocations = true;
            $persons = $personRepository->findWithOffices($bishopquery, self::PAGE_SIZE, $offset, $addMonasteryLocations);

            return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'pageSize' => self::PAGE_SIZE,
                'offset' => $offset,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState, // obsolete 2021-08-24 !?
                'facetOfficesState' => $facetOfficesState,
            ]);

        } else {
            # dd($form, $facetPlacesState, $facetOfficesState);
            return $this->render('query_bishop/launch_query.html.twig', [
                'query_form' => $form->createView(),
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
            ]);
        }
    }

    /**
     * return list result
     *
     * @Route("/bischoefe/_list", name="bishop_list")
     */
    public function _list(Request $request, PersonRepository $personRepository) {

        $offset = $request->request->get('offset') ?? 0;
        $offset = (int) floor($offset / self::PAGE_SIZE) * self::PAGE_SIZE;

        $addMonasteryLocations = true;
        $bishopquery = new BishopQueryFormModel();
        $bishopquery->setFieldsByArray($request->request->get('bishop_query_form'));


        $persons = $personRepository->findWithOffices($bishopquery, self::PAGE_SIZE, $offset, $addMonasteryLocations);

        // return new Response('name: '.$bishopquery->name.' diocese: '.$bishopquery->place);

        return $this->render('query_bishop/_list.html.twig', [
            'persons' => $persons,
            'offset' => $offset,
        ]);

    }


    /**
     * obsolete see IDController.php
     * @Route("/bishop/{wiagidlong}", name="bishop")
     */
    public function getperson($wiagidlong, Request $request) {

        $format = $request->query->get('format');

        if(!is_null($format)) {
            return $this->redirectToRoute('bishop_api', [
                'wiagidlong' => $wiagidlong,
                'format' => $format,
            ]);
        }

        $flaglist = $request->query->get('flaglist');

        $id = Person::wiagidLongToWiagid($wiagidlong);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($id);

        if (!$person) {
            $this->createNotFoundException('Person wurde nicht gefunden');
        }

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $wiagidlong,
            'flaglist' => $flaglist,
            'querystr' => null,
        ]);
    }

    /**
     * obsolete, see getBishopInQuery
     * @Route("/bishop-in-list", name="bishop_in_list")
     */
    public function bishopInList(Request $request) {

        $offset = $request->query->get('offset');

        $bishopquery = new BishopQueryFormModel();
        $bishopquery->setByRequest($request);
        $querystr = http_build_query($bishopquery->toArray());

        $personRepository = $this->getDoctrine()
                                 ->getRepository(Person::class);
        $hassuccessor = false;
        $nextname = null;
        $previousname = null;
        if($offset == 0) {
            $persons = $personRepository->findWithOffices($bishopquery, 2, $offset);
            $iterator = $persons->getIterator();
            if(count($iterator) == 2) $hassuccessor = true;

        } else {
            $persons = $personRepository->findWithOffices($bishopquery, 3, $offset - 1);
            $iterator = $persons->getIterator();
            if(count($iterator) == 3) $hassuccessor = true;
            $previousname = $iterator->current()->getDisplayname();
            $iterator->next();
        }
        $person = $iterator->current();
        if($hassuccessor) {
                $iterator->next();
                $nextname = $iterator->current()->getDisplayname();
        }

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $person->getWiagidlong(),
            'flaglist' => null,
            'querystr' => $querystr,
            'previousname' => $previousname,
            'nextname' => $nextname,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
        ]);

    }

    /**
     * display details for a bishop in a query result list
     *
     * @param object $form                             query form
     * @param int $offset                              offset of the bishop in a query result list
     */
    public function getBishopInQuery(object $form, int $offset) {

        $bishopquery = $form->getData();

        $personRepository = $this->getDoctrine()
                                 ->getRepository(Person::class);
        $hassuccessor = false;
        if($offset == 0) {
            $persons = $personRepository->findWithOffices($bishopquery, 2, $offset);
            $iterator = $persons->getIterator();
            if(count($iterator) == 2) $hassuccessor = true;

        } else {
            $persons = $personRepository->findWithOffices($bishopquery, 3, $offset - 1);
            $iterator = $persons->getIterator();
            if(count($iterator) == 3) $hassuccessor = true;
            $iterator->next();
        }
        $person = $iterator->current();

        // fetch data from domherren database or GS (Personendatenbank)
        $cnonlineRepository = $this->getDoctrine()
                                   ->getRepository(CnOnline::class);
        $cnonline = $cnonlineRepository->findOneByIdEp($person->getWiagid());
        $canon = null;
        $canon_gs = null;
        if (!is_null($cnonline)) {
            $cnonlineRepository->fillData($cnonline);
            $canon = $cnonline->getCanonDh();
            $canon_gs = $cnonline->getCanonGs();
        }

        $canon_merged = array();
        if (!is_null($canon)) {
            $cycle = 1;
            $canon_merged = $this->getDoctrine()
                                 ->getRepository(Canon::class)
                                 ->collectMerged($canon_merged, $canon, $cycle);
            array_unshift($canon_merged, $canon);
        }

        return $this->render('query_bishop/details.html.twig', [
            'query_form' => $form->createView(),
            'person' => $person,
            'wiagidlong' => $person->getWiagidlong(),
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'canon' => $canon,
            'canon_merged' => $canon_merged,
            'canon_gs' => $canon_gs,
        ]);

    }



    /**
     * @Route("/query-test/{id}")
     */
    public function queryid($id) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneByWiagid($id);
        dd($person, self::PAGE_SIZE);
    }

    /**
     * @Route("/query-test/apiname/{name}")
     */
    public function apiname($name) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->suggestName($name);
        dd($person);
    }

    /**
     * @Route("/query-test/person/{id}")
     */
    public function querytestperson($id) {
        $person = $this->getDoctrine()
                      ->getRepository(Person::class)
                      ->findtest($id);
        dd($person);
    }

    /**
     * @Route("/query-test/monastery/{id}")
     */
    public function querytestmonastery($id) {
        $monastery = $this->getDoctrine()
                      ->getRepository(Monastery::class)
                      ->findOneByWiagid($id);
        dd($monastery);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/query-bishops/tests", name="bishop_tests")
     */
    public function samplelist() {

        return $this->render('query_bishop/tests.html.twig');
    }


}
