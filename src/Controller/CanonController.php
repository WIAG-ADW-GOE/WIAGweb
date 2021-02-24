<?php
namespace App\Controller;

use App\Form\CanonFormType;
use App\Form\Model\CanonFormModel;
use App\Entity\Canon;
use App\Repository\CanonRepository;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;

use App\Service\CSVData;
use App\Service\JSONData;
use App\Service\RDFData;
use App\Service\JSONLDData;

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
class CanonController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 20;

    /**
     * @Route("/domherren-schwerin", name="canons_schwerin")
     */
    public function launch_query(Request $request,
                                 CanonRepository $repository) {

        // we need to pass an instance of BishopQueryFormModel, because facets depend on it's data
        $bishopquery = new CanonFormModel;

        $form = $this->createForm(CanonFormType::class, $bishopquery);

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

            if($someid && Canon::isWiagidLong($someid)) {
                $bishopquery->someid = Canon::wiagidLongToWiagid($someid);
            }

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getBishopInQuery($form, $singleoffset);
            }


            // get the number of results (without page limit restriction)
            // TODO 
            $count = $this->getDoctrine()
                          ->getRepository(Canon::class)
                          ->countByQueryObject($bishopquery)[1];

            $offset = 0;
            $querystr = null;
            $persons = null;

            if($count > 0 && $form->getClickedButton()) {

                $buttonname = $form->getClickedButton()->getName();
                if($buttonname != 'searchHTML') {
                    $persons = $repository->findWithOffices($bishopquery);
                    $baseurl = $request->getSchemeAndHttpHost();
                    $response = new Response();

                    switch($buttonname) {
                    case 'searchJSON':
                        $data = $jsondata->canonsToJSON($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/json;charset=UTF-8');
                        break;
                    case 'searchCSV':
                        $data = $csvdata->canonsToCSV($persons, $baseurl);
                        $response->headers->set('Content-Type', "text/csv; charset=utf-8");
                        $response->headers->set('Content-Disposition', "filename=WIAG-Pers-EPISCGatz.csv");
                        break;
                    case 'searchRDF':
                        $data = $rdfdata->canonsToRdf($persons, $baseurl);
                        $response->headers->set('Content-Type', 'application/rdf+xml;charset=UTF-8');
                        break;
                    case 'searchJSONLD':
                        $data = $jsonlddata->canonsToJSONLD($persons, $baseurl);
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
            
            # TODO
            $persons = $repository->findWithOffices($bishopquery, self::LIST_LIMIT, $offset);
            # $persons = $repository->findByGivenname('Albert');

            # TODO
            foreach($persons as $p) {
                if(false && $p->hasMonastery()) {
                    $repository->addMonasteryLocation($p);
                }
                $p->offices = [];
            }


            // combination of POST_SET_DATA and POST_SUBMIT
            // $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

            return $this->render('canon/listresult.html.twig', [
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
            return $this->render('canon/launch_query.html.twig', [
                'query_form' => $form->createView(),
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
            ]);
        }
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

        return $this->render('canon/details.html.twig', [
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
     * @Route("canon-autocomplete-names", name="canon_autocomplete_names")
     */
    public function autocompletenames(Renquest $request) {
        # TODO 2021-02-24
        return $this->json([
            'names' => ['Eule'],
        ]);
    }

    /**
     * AJAX callback
     * @Route("canon-autocomplete-places", name="canon_autocomplete_places")
     */
    public function autocompleteplaces(Renquest $request) {
        # TODO 2021-02-24
        return $this->json([
            'places' => ['Eulenburg'],
        ]);
    }

    /**
     * AJAX callback
     * @Route("canon-autocomplete-offices", name="canon_autocomplete_offices")
     */
    public function autocompleteoffices(Renquest $request) {
        # TODO 2021-02-24
        return $this->json([
            'offices' => ['Eulenamt'],
        ]);
    }




}
