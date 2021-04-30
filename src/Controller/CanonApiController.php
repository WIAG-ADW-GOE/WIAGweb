<?php

namespace App\Controller;

use App\Entity\CnOnline;
use App\Form\CanonFormType;
use App\Form\Model\CanonFormModel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 */
class CanonApiController extends AbstractController {

    const LIST_LIMIT = 20;

    /**
     * @Route("/api/domherren", name="api_query_canons")
     */
    public function listcanons(Request $request) {

        $format = $request->query->get('format') ?? 'html';

        if(array_search($format, ['html']) === false) {
            // TODO set up error pages
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        $name = $request->query->get('name');
        $monastery= $request->query->get('domstift');
        $office = $request->query->get('amt');
        $place = $request->query->get('ort');
        $year = $request->query->get('jahr');
        $someid = $request->query->get('nummer');

        $queryformdata = new CanonFormModel($name, $monastery, $office, $place, $year, $someid);

        $form = $this->createForm(CanonFormType::class, $queryformdata, [
            'force_facets' => true,
        ]);

        $offset = 0;

        $repository = $this->getDoctrine()->getRepository(CnOnline::class);
        // dd($queryformdata);
        $count = $repository->countByQueryObject($queryformdata)[1];
        $persons = $repository->findByQueryObject($queryformdata, self::LIST_LIMIT, $offset);

        foreach($persons as $p) {
            /* It may look strange to do queries in a loop, but we have two data sources.
               The list is not long (LIST_LIMIT).
            */
            $repository->fillListData($p);
        }

        // combination of POST_SET_DATA and POST_SUBMIT
        // $form = $this->createForm(BishopQueryFormType::class, $bishopquery);

        return $this->render('canon/listformresult.html.twig', [
            'query_form' => $form->createView(),
            'count' => $count,
            'limit' => self::LIST_LIMIT,
            'offset' => $offset,
            'persons' => $persons,
        ]);

    }
}
