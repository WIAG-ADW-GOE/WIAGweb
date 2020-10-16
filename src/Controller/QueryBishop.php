<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Entity\PlaceCount;


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
    const LIST_LIMIT = 20;


    /**
     * @Route("/query-bishops", name="launch_query")
     */
    public function launch_query(Request $request) {

        // we need to pass an instance of BishopQueryFormModel, because facets depend on it's data
        $bishopquery = new BishopQueryFormModel;
        $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

        // $form = $this->createForm(BishopQueryFormType::class, array());

        $form->handlerequest($request);

        // $facetPlacesState = 'hide';
        // $facetOfficesState = 'hide';
        $facetPlacesState = 'show';
        $facetOfficesState = 'show';

        if ($form->isSubmitted() && $form->isValid()) {

            $bishopquery = $form->getData();
            $someid = $bishopquery->someid;

            if($someid && Person::isWiagidLong($someid)) {
                $bishopquery->someid = Person::wiagidLongToWiagid($someid);
            }

            // get the number of results (without page limit restriction)
            $count = $this->getDoctrine()
                          ->getRepository(Person::class)
                          ->countByQueryObject($bishopquery)[1];

            $page = 0;
            $persons = null;

            if($count > 0) {
                if($form->get('searchJSON')->isClicked()) {
                    $persons = $this->getDoctrine()
                                    ->getRepository(Person::class)
                                    ->findWithOffices($bishopquery);

                    $personExports = array();
                    foreach($persons as $p) {
                        $personExports[] = $p->toJSON();
                    }

                    return $this->json(array('persons' => $personExports));
                }


                $page = $request->request->get('page') ?? 1;

                $persons = $this->getDoctrine()
                                ->getRepository(Person::class)
                                ->findWithOffices($bishopquery, self::LIST_LIMIT, $page);
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

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $wiagidlong,
            'wikipediatitle' => $wikipediatitle,
            'flaglist' => $flaglist,
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

}
