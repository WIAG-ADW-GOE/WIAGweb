<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Entity\Familynamevariant;
use App\Entity\Givennamevariant;
use App\Entity\PlaceCount;


use Ds\Set;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @IsGranted("ROLE_QUERY")
 */
class QueryBishop extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 30;
    const WIAGID_PREFIX = 'WIAG-Pers-EPISCGatz-';
    const WIAGID_POSTFIX = '-001';


    /**
     * @Route("/query-bishops", name="launch_query")
     */
    public function launch_query(Request $request) {

        // we need to pass an instance of BishopQueryFormModel, because facets depend on it's data
        $bishopquery = new BishopQueryFormModel;
        $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

        // $form = $this->createForm(BishopQueryFormType::class, array());

        $form->handlerequest($request);

        $facetPlacesState = 'hide';
        $facetOfficesState = 'hide';

        if ($form->isSubmitted() && $form->isValid()) {

            $bishopquery = $form->getData();

            $page = $request->request->get('page');
            if (!$page) {
                $page = 1;
            }

            // get the number of results (without page limit restriction)
            $count = $this->getDoctrine()
                          ->getRepository(Person::class)
                          ->countByQueryObject($bishopquery)[0]['count'];

            $persons = array();
            if ($count > 0) {
                $facetPlacesState = $request->request->get('facetPlacesState');


                $persons = $this->getDoctrine()
                            ->getRepository(Person::class)
                            ->findPersonsAndOffices($bishopquery, self::LIST_LIMIT, $page);
                dump($persons[0]);
                foreach($persons as $key => $person) {
                    $persons[$key]['wiagidlong'] = self::WIAGID_PREFIX.$person['wiagid'].self::WIAGID_POSTFIX;
                }
            }

            $places = array();
            if ($count > 0) {
                $places_raw = $this->getDoctrine()
                                   ->getRepository(Person::class)->findPlacesByQueryObject($bishopquery);
                foreach ($places_raw as $pl) {
                    $places[] = new PlaceCount($pl['diocese'], $pl['n']);
                }
            }

            // combination of POST_SET_DATA and POST_SUBMIT
            // $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

            return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'page' => $page,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
                'places' => $places,
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
     * @Route("/bishop/{wiagidlong}", name="bishop")
     */
    public function getperson($wiagidlong) {

        // remove prefix and suffix
        $pos = strlen(self::WIAGID_PREFIX);
        $id = substr($wiagidlong, $pos, -4);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                        ->findOneByWiagid($id);

        if (!$person) {
            $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $wikipediaurl = $person->getWikipediaurl();
        $wikipediaurlbase = 'https://de.wikipedia.org/wiki/';
        $wikipediaurlp = explode($wikipediaurlbase, $wikipediaurl);
        /**
         * prüfe ob es ein element 1 gibt array_has_key
         * lies es aus
         * wende urldecode an
         * wende str_replace('_', ' ', wikipediadisplay) an.
         */
        $wikipediatitle = $wikipediaurl;
        if (count($wikipediaurlp) > 1) {
            $wpurl_decoded = urldecode($wikipediaurlp[1]);
            $wikipediatitle = str_replace('_', ' ', $wpurl_decoded);
        }

        $offices = $this->getDoctrine()
                        ->getRepository(Office::class)
                        ->findByIDPerson($id);

        $familynamevariants = $this->getDoctrine()
                                   ->getRepository(Familynamevariant::class)
                                   ->findByWiagid($id);

        $givennamevariants = $this->getDoctrine()
                                   ->getRepository(Givennamevariant::class)
                                   ->findByWiagid($id);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $wiagidlong,
            'offices' => $offices,
            'familynamevariants' => $familynamevariants,
            'givennamevariants' => $givennamevariants,
            'hasNormdata' => $person->hasNormdata(),
            'wikipediatitle' => $wikipediatitle,
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


}
