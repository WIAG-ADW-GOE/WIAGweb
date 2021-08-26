<?php

namespace App\Controller;

use App\Entity\Namelookup;
use App\Entity\Office;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * AJAX callbacks for bishops
 */
class QueryBishopUtility extends AbstractController {

    /** number of items in completion list */
    const HINT_LIST_LIMIT = 12;

    /**
     * AJAX callback
     *
     * @Route("/query-bishops/utility/names", methods="GET", name="query_bishops_utility_names")
     */
    public function getNamesApi(Request $request) {
        $suggestions = $this->getDoctrine()
                            ->getRepository(Namelookup::class)
                            ->suggestName($request->query->get('q'),
                                          self::HINT_LIST_LIMIT);

        // return $this->json([
        //     'names' => $suggestions,
        // ]);

        return $this->render('query_bishop/_autocomplete.twig.html', [
            'suggestions' => array_column($suggestions, 'suggestion'),
        ]);

    }

    /**
     * AJAX callback
     *
     *@Route("/query-bishops/utility/places", methods="GET", name="query_bishops_utility_places")
     */
    public function getPlacesApi(Request $request) {
        $query = trim($request->query->get('q'));
        # strip 'bistum' or 'erzbistum'
        foreach(['bistum', 'erzbistum', 'Bistum', 'Erzbistum'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs))
                $query = trim(str_replace($bs, "", $query));
        }

        $places = $this->getDoctrine()
                       ->getRepository(Office::class)
                       ->suggestPlace($query, self::HINT_LIST_LIMIT);

        return $this->render('query_bishop/_autocomplete.twig.html', [
            'suggestions' => array_column($places, 'suggestion'),
        ]);
    }

    /**
     * AJAX callback
     *
     *@Route("/query-bishops/utility/offices", methods="GET", name="query_bishops_utility_offices")
     */
    public function getOfficeApi(Request $request) {
        $offices = $this->getDoctrine()
                       ->getRepository(Office::class)
                       ->suggestOffice($request->query->get('q'),
                                                    self::HINT_LIST_LIMIT);

        return $this->render('query_bishop/_autocomplete.twig.html', [
            'suggestions' => array_column($offices, 'suggestion'),
        ]);
    }


}
