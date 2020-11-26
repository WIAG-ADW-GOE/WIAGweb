<?php

namespace App\Controller;

use App\Entity\Diocese;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_QUERY")
 */
class DioceseUtility extends AbstractController {
    /**
     * Parameters
     */
    const HINT_LIST_LIMIT = 12;

    /**
     * AJAX callback
     *@Route("/query-dioceses/utility/names", methods="GET", name="query_dioceses_utility_names")
     */
    public function getNamesApi(Request $request) {
        $query = trim($request->query->get('query'));
        # strip 'bistum' or 'erzbistum'
        foreach(['bistum', 'erzbistum', 'Bistum', 'Erzbistum'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs))
                $query = trim(str_replace($bs, "", $query));
        }

        $names = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->suggestDiocese($query, self::HINT_LIST_LIMIT);

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
