<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;
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
 * @IsGranted("ROLE_QUERY")
 */
class QueryBishop extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 20;

    /**
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
            $someid = $bishopquery->someid;

            # strip 'Bistum' or 'Erzbistum'
            $bishopquery->normPlace();

            if($someid && Person::isWiagidLong($someid)) {
                $bishopquery->someid = Person::wiagidLongToWiagid($someid);
            }

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getBishopInQuery($form, $singleoffset);
            }


            // get the number of results (without page limit restriction)
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
            if($count < self::LIST_LIMIT) $offset = 0;

            $offset = (int) floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;
            $persons = $personRepository->findWithOffices($bishopquery, self::LIST_LIMIT, $offset);

            foreach($persons as $p) {
                if($p->hasMonastery()) {
                    $personRepository->addMonasteryLocation($p);
                }
            }


            // combination of POST_SET_DATA and POST_SUBMIT
            // $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

            return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState,
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
     * obsolete: use POST requests instead
     * @Route("/requery-bishops", name="requery_bishops")
     */
    public function reloadForm(Request $request) {
        $bishopquery = new BishopQueryFormModel();
        $bishopquery->setByRequest($request);
        // querystr without offset
        $querystr = http_build_query($bishopquery->toArray());
        $form = $this->createForm(BishopQueryFormType::class, $bishopquery);


        $someid = $bishopquery->someid;

        if($someid && Person::isWiagidLong($someid)) {
            $bishopquery->someid = Person::wiagidLongToWiagid($someid);
        }

        // get the number of results (without page limit restriction)
        $count = $this->getDoctrine()
                      ->getRepository(Person::class)
                      ->countByQueryObject($bishopquery)[1];

        $facetPlacesState = 'show';
        $facetOfficesState = 'show';
        $persons = null;

        if($count > 0) {
            $personRepository = $this->getDoctrine()
                                     ->getRepository(Person::class);
            $offset = $request->query->get('offset') ?? 0;
            # map to pages
            $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $persons = $personRepository->findWithOffices($bishopquery, self::LIST_LIMIT, $offset);

            foreach($persons as $p) {
                if($p->hasMonastery()) {
                    $personRepository->addMonasteryLocation($p);
                }
            }
        }

        return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'querystr' => $querystr,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
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

        $dioceseRepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $wiagidlong,
            'flaglist' => $flaglist,
            'querystr' => null,
            'dioceserepository' => $dioceseRepository,
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

        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $person->getWiagidlong(),
            'flaglist' => null,
            'querystr' => $querystr,
            'previousname' => $previousname,
            'nextname' => $nextname,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'dioceserepository' => $dioceseRepository,
        ]);

    }

    public function getBishopInQuery($form, $offset) {

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

        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'query_form' => $form->createView(),
            'person' => $person,
            'wiagidlong' => $person->getWiagidlong(),
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'dioceserepository' => $dioceseRepository,
        ]);

    }



    /**
     * @Route("/query-test/{id}")
     */
    public function queryid($id) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneByWiagid($id);
        dd($person, self::LIST_LIMIT);
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


}
