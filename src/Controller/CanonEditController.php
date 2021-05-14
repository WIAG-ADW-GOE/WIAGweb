<?php
namespace App\Controller;

use App\Form\CanonEditSearchFormType;
use App\Form\Model\CanonEditSearchFormModel;
use App\Entity\CnOnline;
use App\Entity\Canon;
use App\Entity\CnNamelookup;
use App\Entity\CnOfficelookup;
use App\Repository\CanonRepository;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;
use App\Service\CanonData;
use App\Service\CanonLinkedData;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CanonEditController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 20;
    const HINT_LIST_LIMIT = 12;

    /**
     * @Route("/domherren/edit", name="canons_edit")
     */
    public function launch_query(Request $request) {

        $querydata = new CanonEditSearchFormModel;

        $form = $this->createForm(CanonEditSearchFormType::class, $querydata);
        
        $form->handlerequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $repository = $this->getDoctrine()
                               ->getRepository(Canon::class);

            $querydata = $form->getData();

            // get the number of results (without page limit restriction)
            $count = $repository->countByEditQueryObject($querydata)[1];

            // return HTML

            $offset = $request->request->get('offset') ?? 0;

            // extra check to avoid empty lists
            if($count < self::LIST_LIMIT) $offset = 0;

            $offset = (int) floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $persons = $repository->findByEditQueryObject($querydata, self::LIST_LIMIT, $offset);


            return $this->render('canon/listeditform.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'persons' => $persons,
            ]);

        } else {
            // show empty form only
            return $this->render('canon/launchlisteditform.html.twig', [
                'query_form' => $form->createView(),
            ]);
        }
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
            'names' => $suggestions,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren-wd/autocomplete/monastery", name="canon_autocomplete_monastery")
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
                            ->suggestPlace($query, self::HINT_LIST_LIMIT);
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
