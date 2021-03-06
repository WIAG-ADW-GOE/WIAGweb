<?php
namespace App\Controller;

use App\Form\CanonFormType;
use App\Form\Model\CanonFormModel;
use App\Entity\CnOnline;
use App\Entity\Canon;
use App\Entity\CnNamelookup;
use App\Entity\CnOfficelookup;
use App\Repository\CanonRepository;
use App\Repository\CnOnlineRepository;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;
use App\Service\CanonService;
use App\Service\CanonData;
use App\Service\CanonLinkedData;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CanonController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 20;
    const HINT_LIST_LIMIT = 12;

    /**
     * @Route("/domherren", name="query_canons")
     */
    public function launch_query(Request $request,
                                 CnOnlineRepository $repository,
                                 CanonService $cs,
                                 CanonData $canonData,
                                 CanonLinkedData $canonLinkedData) {

        $queryformdata = new CanonFormModel;

        if (count($request->request->all()) > 0) {
            $form = $this->createForm(CanonFormType::class, $queryformdata);
        } else {
            $form = $this->createForm(CanonFormType::class, $queryformdata, [
                'force_facets' => true,
            ]);
        }
        $form->handlerequest($request);


        // $facetInstitutionsState = 'hide';
        // $facetOfficesState = 'hide';
        $facetInstitutionsState = 'show';
        $facetOfficesState = 'show';

        // if ($form->isSubmitted() && $form->isValid()) {
        if (true) {

            $queryformdata = $form->getData() ?? new CanonFormModel;

            # strip 'Bistum' or 'Erzbistum'
            $queryformdata->normPlace();

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getCanonInQuery($form, $singleoffset, $cs);
            }

            // get the number of results (without page limit restriction)
            $count = $repository->countByQueryObject($queryformdata)[1];

            $offset = 0;
            $querystr = null;
            $persons = null;

            if($count > 0 && $form->getClickedButton()) {

                $buttonname = $form->getClickedButton()->getName();
                if($buttonname != 'searchHTML') {
                    $persons = $repository->findByQueryObject($queryformdata);
                    $baseurl = $request->getSchemeAndHttpHost();
                    $response = new Response();

                    switch($buttonname) {
                    case 'searchJSON':
                        $data = $canonData->canonsToJSON($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
                        break;
                    case 'searchCSV':
                        $data = $canonData->canonsToCSV($persons, $baseurl);
                        $response->headers->set('Content-Type', "text/csv; charset=utf-8");
                        $response->headers->set('Content-Disposition', "filename=WIAG-Pers-EPISCGatz.csv");
                        break;
                    case 'searchRDF':
                        $data = $canonLinkedData->canonsToRdf($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
                        break;
                    case 'searchJSONLD':
                        $data = $canonLinkedData->canonsToJSONLD($persons, $baseurl);
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

            $persons = $repository->findByQueryObject($queryformdata, self::LIST_LIMIT, $offset);

            foreach($persons as $p) {
                /* It may look strange to do queries in a loop, but we have two data sources.
                   The list is not long (LIST_LIMIT).
                 */
                $repository->fillListData($p);
            }

            return $this->render('canon/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'persons' => $persons,
                'facetInstitutionsState' => $facetInstitutionsState,
                'facetOfficesState' => $facetOfficesState,
            ]);

        } else {
            // show empty form only
            return $this->render('canon/launch_query.html.twig', [
                'query_form' => $form->createView(),
                'facetInstitutionsState' => $facetInstitutionsState,
                'facetOfficesState' => $facetOfficesState,
            ]);
        }
    }


    public function getCanonInQuery($form, $offset, CanonService $cs) {

        $queryformdata = $form->getData();

        $personRepository = $this->getDoctrine()
                                 ->getRepository(CnOnline::class);
        $hassuccessor = false;
        if($offset == 0) {
            $persons = $personRepository->findByQueryObject($queryformdata, 2, $offset);
            $iterator = $persons->getIterator();
            if(count($iterator) == 2) $hassuccessor = true;

        } else {
            $persons = $personRepository->findByQueryObject($queryformdata, 3, $offset - 1);
            $iterator = $persons->getIterator();
            if(count($iterator) == 3) $hassuccessor = true;
            $iterator->next();
        }
        $person = $iterator->current();

        $personRepository->fillData($person);

        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        $canon_dh = $person->getCanonDh();
        $references = array();
        if (!is_null($canon_dh)) {
            $cycle = 1;
            $references = $cs->collectMerged($references, $canon_dh, $cycle);
            array_unshift($references, $canon_dh);
        }

        return $this->render('canon/details.html.twig', [
            'query_form' => $form->createView(),
            'person' => $person,
            'wiagidlong' => $person->getId(),
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'dioceserepository' => $dioceseRepository,
            'references' => $references,
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
     * @Route("/canon-test/{id}")
     */
    public function canontest($id) {
        $canon = $this->getDoctrine()
                      ->getRepository(Canon::class)
                      ->find($id);
        dd($canon);
    }

    /**
     * AJAX callback
     * @Route("domherren-wd/autocomplete/name", name="canon_autocomplete_name")
     */
    public function autocompletenames(Request $request) {
        $suggestions = $this->getDoctrine()
                            ->getRepository(CnNamelookup::class)
                            ->suggestName($request->query->get('query'),
                                          self::HINT_LIST_LIMIT);

        return $this->json([
            'choices' => $suggestions,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren-wd/autocomplete/monastery", name="canon_autocomplete_domstift")
     */
    public function autocompletemonastery(Request $request) {
        $query = trim($request->query->get('query'));
        # strip 'bistum' or 'erzbistum'
        foreach(['Stift', 'Domstift'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs)) {
                $query = trim(str_replace($bs, "", $query));
                break;
            }
        }

        $monasteries = $this->getDoctrine()
                            ->getRepository(Monastery::class)
                            ->suggestDomstift($query, self::HINT_LIST_LIMIT);
        return $this->json([
            'monasteries' => $monasteries,
        ]);
    }


    /**
     * AJAX callback
     * @Route("domherren-wd/autocomplete/place", name="canon_autocomplete_place")
     */
    public function autocompleteplace(Request $request) {
        $query = trim($request->query->get('query'));
        # strip 'bistum' or 'erzbistum'
        foreach(['Stift', 'Domstift'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs)) {
                $query = trim(str_replace($bs, "", $query));
                break;
            }
        }

        $places = $this->getDoctrine()
                       ->getRepository(CnOfficelookup::class)
                       ->suggestPlace($query, self::HINT_LIST_LIMIT);
        return $this->json([
            'places' => $places,
        ]);
    }


    /**
     * AJAX callback
     * @Route("domherren-wd/autocomplete/office", name="canon_autocomplete_office")
     */
    public function autocompleteoffices(Request $request) {
        $offices = $this->getDoctrine()
                        ->getRepository(CnOfficelookup::class)
                        ->suggestOffice($request->query->get('query'),
                                        self::HINT_LIST_LIMIT);

        return $this->json([
            'offices' => $offices,
        ]);
    }


}
