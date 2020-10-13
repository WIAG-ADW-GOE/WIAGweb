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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


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

            if($form->get('searchJSON')->isClicked()) {
                return $this->redirectToRoute('api_query_bishops', $bishopquery->getQueryArray());
            }

            // get the number of results (without page limit restriction)
            $count = $this->getDoctrine()
                          ->getRepository(Person::class)
                          ->countByQueryObject($bishopquery)[0]['count'];

            $page = 0;
            $persons = null;
            
            if ($count > 0) {
                $page = $request->request->get('page') ?? 1;

                if(!$bishopquery->name && $bishopquery->place) {
                    $persons = $this->getDoctrine()
                                    ->getRepository(Person::class)
                                    ->findByPlaceWithOffices($bishopquery, self::LIST_LIMIT, $page);
                } 


                $someid = $bishopquery->someid;
                if($someid and Person::isWiagidLong($someid)) {
                    $bishopquery->updateSomeid();
                }

                // $persons = $this->getDoctrine()
                //             ->getRepository(Person::class)
                //             ->findWithOffices($bishopquery, self::LIST_LIMIT, $page);
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
        
        if(! is_null($format)) {
            return $this->redirectToRoute('bishop_api', [
                'wiagidlong' => $wiagidlong,
                'format' => $format,
            ]);
        }

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

        // $familynamevariants = $this->getDoctrine()
        //                            ->getRepository(Familynamevariant::class)
        //                            ->findByWiagid($id);

        // $givennamevariants = $this->getDoctrine()
        //                            ->getRepository(Givennamevariant::class)
        //                            ->findByWiagid($id);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $wiagidlong,
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

    /**
     * @Route("/query-test/office/{id}")
     */
    public function queryoffice($id) {
        $office = $this->getDoctrine()
                       ->getRepository(Office::class)
                       ->findByWiagid($id);
        dd($office);
    }

    

}
