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
 * @IsGranted("ROLE_QUERY")
 */
class QueryBishopUtility extends AbstractController {
    /**
     * Paramters
     */
    const HINT_LIST_LIMIT = 12;

    /**
     * AJAX callback
     * @Route("/query-bishops/utility/names", methods="GET", name="query_bishops_utility_names")
     */
    public function getNamesApi(Request $request) {
        $suggestions = $this->getDoctrine()
                            ->getRepository(Namelookup::class)
                            ->suggestName($request->query->get('query'),
                                          self::HINT_LIST_LIMIT);

        return $this->json([
            'names' => $suggestions,
        ]);
    }

    /**
     * AJAX callback
     *@Route("/query-bishops/utility/places", methods="GET", name="query_bishops_utility_places")
     */
    public function getPlacesApi(Request $request) {
        $places = $this->getDoctrine()
                       ->getRepository(Office::class)
                       ->suggestPlace($request->query->get('query'),
                                      self::HINT_LIST_LIMIT);
        return $this->json([
            'places' => $places,
        ]);
    }

    /**
     * AJAX callback
     *@Route("/query-bishops/utility/offices", methods="GET", name="query_bishops_utility_offices")
     */
    public function getOfficeApi(Request $request) {
        $offices = $this->getDoctrine()
                       ->getRepository(Office::class)
                       ->suggestOffice($request->query->get('query'),
                                                    self::HINT_LIST_LIMIT);

        return $this->json([
            'offices' => $offices,
        ]);
    }


}
