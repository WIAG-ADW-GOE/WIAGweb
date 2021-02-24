<?php

namespace App\Controller;

use App\Entity\Domstift;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_QUERY")
 */
class CanonGSUtility extends AbstractController {
    /**
     * Parameters
     */
    const HINT_LIST_LIMIT = 12;

    /**
     * AJAX callback
     *@Route("/query-canons/utility/stiftnames", methods="GET", name="query_canons_utility_stiftnames")
     */
    public function getNamesApi(Request $request) {
        $query = trim($request->query->get('query'));
        # strip 'bistum' or 'erzbistum'
        foreach(['bistum', 'erzbistum', 'Bistum', 'Erzbistum'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs))
                $query = trim(str_replace($bs, "", $query));
        }


        $names = $this->getDoctrine()
                        ->getRepository(Domstift::class)
                        ->suggestDomstift($query, self::HINT_LIST_LIMIT);

        // $names = [
        //     ['suggestion' => 'Bamberg'],
        //     ['suggestion' => 'Köln'],
        //     ['suggestion' => 'Limburg'],
        // ];
        return $this->json([
            'names' => $names,
        ]);
    }

}
