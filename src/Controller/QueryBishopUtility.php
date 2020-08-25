<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use App\Repository\OfficeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class QueryBishopUtility extends AbstractController {
    /** 
     * Paramters
     */
    const HINT_LIST_LIMIT = 12;

    /** 
     *@Route("/query-bishops/utility/names", methods="GET", name="query_bishops_utility_names")
     */
    public function getNamesApi(PersonRepository $personRepository, Request $request) {
        $persons = $personRepository->suggestName($request->query->get('query'),
                                                  self::HINT_LIST_LIMIT);
        // $persons = $personRepository->findByFamilyname("ege");
        return $this->json([
            'persons' => $persons,
        ]);
    }

        /** 
     *@Route("/query-bishops/utility/places", methods="GET", name="query_bishops_utility_places")
     */
    public function getPlacesApi(OfficeRepository $officeRepository, Request $request) {
        $places = $officeRepository->suggestPlace($request->query->get('query'),
                                                  self::HINT_LIST_LIMIT);
        return $this->json([
            'places' => $places,
        ]);
    }

}
