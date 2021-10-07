<?php

namespace App\Controller;

use App\Entity\Diocese;
use App\Entity\Reference;
use App\Service\DioceseData;
use App\Service\DioceseLinkedData;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * query dioceses
 */
class DioceseController extends AbstractController {
    /** number of items per page */
    const LIST_LIMIT = 25;

    /**
     * list dioceses; select by initial letter
     *
     * 2021-07-15 not navigation menu
     *
     * @Route("/list-dioceses", name="list_dioceses")
     */
    public function listDioceses (Request $request) {

        $query = $request->query;
        $offset = $query->get('offset') ?? 0;
        $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;
        $initialletter = $query->get('name') ?? 'A-Z';

        $repository = $this->getDoctrine()
                           ->getRepository(Diocese::class);

        $count = $repository->countByInitalletter($initialletter);

        $dioceses = $repository->findByInitialLetterWithBishopricSeat($initialletter, self::LIST_LIMIT, $offset);


        return $this->render('query_diocese/list.html.twig', [
            'dioceses' => $dioceses,
            'offset' => $offset,
            'count' => $count,
            'name' => $initialletter,
            'limit' => self::LIST_LIMIT,
        ]);
    }


    /**
     * display query form for dioceses; handle query
     *
     * only field: name
     *
     * @Route("/bistuemer", name="query_dioceses")
     */
    public function dioceses (Request $request,
                              DioceseData $dioceseData,
                              DioceseLinkedData $dioceseLinkedData) {

        $diocesequery = new Diocese();

        $route_utility_names = $this->generateUrl('query_dioceses_utility_names');

        $form = $this->createFormBuilder($diocesequery)
                     ->add('diocese', TextType::class, [
                         'label' => false,
                         'required' => false,
                         'attr' => [
                             'placeholder' => 'Erzbistum/Bistum',
                             'class' => 'js-name-autocomplete',
                             'data-autocomplete-url' => $route_utility_names,
                             'size' => 25,
                         ],
                     ])
                     ->add('searchHTML', SubmitType::class, [
                         'label' => 'Suche',
                         'attr' => [
                             'class' => 'btn btn-secondary btn-light',
                         ],
                     ])
                     ->getForm();

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $diocesequery = $form->getData();

            # strip 'bistum' or 'erzbistum' from search field diocese
            $diocesequery->normDiocese();

            $offset = $request->request->get('offset') ?? 0;
            $format = $request->request->get('format') ?? null;

            $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $singleoffset = $request->request->get('singleoffset');

            $name = $diocesequery->getDiocese();

            $repository = $this->getDoctrine()
                               ->getRepository(Diocese::class);


            if(!is_null($singleoffset)) {
                return $this->getDioceseInQuery($form, $singleoffset);
            }
            else {
                $count = $repository->countByName($name);
                /* In case of a GET-request (list=all) name is empty.
                 * If name is empty return all dioceses.
                 */
                $dioceses = $repository->findByNameWithBishopricSeat($name, self::LIST_LIMIT, $offset);

                if($count > 0) {
                    $baseurl = $request->getSchemeAndHttpHost();
                    switch($format) {
                    case 'CSV':
                        $data = $dioceseData->diocesesToCSV($dioceses, $baseurl);
                        $response =  new Response($data);
                        $response->headers->set('Content-Type', "text/csv; charset=utf-8");
                        $response->headers->set('Content-Disposition', "filename=WIAGDioceses.csv");

                        return $response;
                        break;
                    case 'JSON':
                        $data = $dioceseData->diocesesToJSON($dioceses, $baseurl);
                        $response = new Response();
                        $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
                        $response->setContent($data);

                        return $response;
                        break;
                    case 'RDF':
                        $data = $dioceseLinkedData->diocesesToRdf($dioceses, $baseurl);
                        $response = new Response();
                        $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
                        $response->setContent($data);

                        return $response;
                        break;
                    case 'JSONLD':
                        $data = $dioceseLinkedData->diocesesToJSONLD($dioceses, $baseurl);
                        $response = new Response();
                        $response->headers->set('Content-Type', 'application/ld+json;charset=UTF-8');
                        $response->setContent($data);

                        return $response;
                        break;
                    }
                }
                return $this->render('query_diocese/listresult.html.twig', [
                    'form' => $form->createView(),
                    'dioceses' => $dioceses,
                    'offset' => $offset,
                    'count' => $count,
                    'name' => $name,
                    'limit' => self::LIST_LIMIT,
                ]);
            }
        }
        // if the form is empty show the complete list
        elseif (!$form->isSubmitted()) {
            $offset = 0;
            $repository = $this->getDoctrine()
                               ->getRepository(Diocese::class);
            $name='';
            $count = $repository->countByName($name);
            $dioceses = $repository->findByNameWithBishopricSeat($name, self::LIST_LIMIT, $offset);
            return $this->render('query_diocese/listresult.html.twig', [
                'form' => $form->createView(),
                'dioceses' => $dioceses,
                'offset' => $offset,
                'count' => $count,
                'name' => $name,
                'limit' => self::LIST_LIMIT,
            ]);
        }


        return $this->render('query_diocese/launch_query.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * start with an empty search form
     * [to be completed if neccessary]
     * @Route("/suche-bistuemer", name="query_dioceses_empty")
     */
    public function query(Request $request) {
        $diocesequery = new Diocese();

        $route_utility_names = $this->generateUrl('query_dioceses_utility_names');

        $form = $this->createFormBuilder($diocesequery)
                     ->add('diocese', TextType::class, [
                         'label' => false,
                         'required' => false,
                         'attr' => [
                             'placeholder' => 'Erzbistum/Bistum',
                             'class' => 'js-name-autocomplete',
                             'data-autocomplete-url' => $route_utility_names,
                             'size' => 25,
                         ],
                     ])
                     ->add('searchHTML', SubmitType::class, [
                         'label' => 'Suche',
                         'attr' => [
                             'class' => 'btn btn-secondary btn-light',
                         ],
                     ])
                     ->getForm();

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            # to be completed if neccessary
        }

        return $this->render('query_diocese/launch_query.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * find diocese by id or by name; display details or deliver data as JSON or CSV
     *
     * @Route("/diocese/{idorname}", name="diocese")
     * @todo bring up to date; see DioceseApiController
     */
    public function getDiocese($idorname, Request $request) {

        $format = $request->query->get('format');

        if(!is_null($format)) {
            return $this->redirectToRoute('api_diocese', [
                'wiagid' => $idorname,
                'format' => $format,
            ]);
        }

        $flaglist = $request->query->get('flaglist');

        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($idorname);

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden: {$idorname}");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'flaglist' => $flaglist,
        ]);
    }

    /**
     * display details for a diocese in a query result list
     */
    public function getDioceseInQuery($form, int $offset) {

        # dd($name, $offset);

        $dioceserepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        $name = $form->getData()->getDiocese();

        $hassuccessor = false;
        if($offset == 0) {
            $dioceses = $dioceserepository->findByNameWithBishopricSeat($name, 2, $offset);
            $iterator = $dioceses->getIterator();
            if(count($iterator) == 2) $hassuccessor = true;
        } else {
            $dioceses = $dioceserepository->findByNameWithBishopricSeat($name, 3, $offset - 1);
            $iterator = $dioceses->getIterator();
            if(count($iterator) == 3) $hassuccessor = true;
            $iterator->next();
        }
        $diocese = $iterator->current();

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden.");
        }

        $reference = $this->getDoctrine()
                          ->getRepository(Reference::class)
                          ->find(Diocese::REFERENCE_ID);
        $diocese->setReference($reference);

        return $this->render('query_diocese/details.html.twig', [
            'form' => $form->createView(),
            'diocese' => $diocese,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
        ]);
    }

    /**
     * display details for a diocese in a query result list (query request)
     *
     * [not up to date; obsolete?]
     *
     * @Route("/diocese-in-list/", name="diocese_in_list")
     */
    public function getDioceseInList(Request $request) {

        $format = $request->query->get('format');

        if(!is_null($format)) {
            return $this->redirectToRoute('diocese_api', [
                'wiagid' => $idorname,
                'format' => $format,
            ]);
        }

        $offset = $request->query->get('offset');
        $initialletter = $request->query->get('name');
        if($initialletter != 'A-Z')
            $initialletter = substr($initialletter, 0, 1);

        $dioceserepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        $hassuccessor = false;
        $nextname = null;
        $previousname = null;
        if($offset == 0) {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 2, $offset);
            if(count($dioceses) == 2) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[0] : null;
            if($hassuccessor) $nextname = $dioceses[1]->getDiocese();
        } else {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 3, $offset - 1);
            $previousname = $dioceses ? $dioceses[0]->getDiocese() : null;
            if(count($dioceses) == 3) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[1] : null;
            if($hassuccessor) $nextname = $dioceses[2]->getDiocese();
        }


        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden.");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'previousname' => $previousname,
            'nextname' => $nextname,
            'name' => $initialletter,
            'flaglist' => null,
        ]);
    }

    /**
     * build form from query request
     *
     * 2021-07-15 obsolete; not up to date
     *
     * @Route("/requery-dioceses", name="requery_dioceses")
     */
    public function reloadForm(Request $request) {
        $diocesequery = new Diocese();
        $diocesequery->setByRequest($request);
        // querystr without offset
        $querystr = http_build_query($diocesequery->toArray());
        $form = $this->createForm(DioceseQueryFormType::class, $diocesequery);


        $someid = $diocesequery->someid;

        if($someid && Person::isWiagidLong($someid)) {
            $diocesequery->someid = Person::wiagidLongToWiagid($someid);
        }

        // get the number of results (without page limit restriction)
        $count = $this->getDoctrine()
                      ->getRepository(Person::class)
                      ->countByQueryObject($diocesequery)[1];

        $facetPlacesState = 'show';
        $facetOfficesState = 'show';
        $persons = null;

        if($count > 0) {
            $personRepository = $this->getDoctrine()
                                     ->getRepository(Person::class);
            $offset = $request->query->get('offset') ?? 0;
            # map to pages
            $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $addMonasteryLocations = true;
            $persons = $personRepository->findWithOffices($diocesequery, self::LIST_LIMIT, $offset, $addMonasteryLocations);

        }

        return $this->render('query_diocese/listresult.html.twig', [
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




}
