<?php

namespace App\Controller;

use App\Entity\Diocese;
# use App\Form\DioceseQueryFormType;
# use App\Form\Model\DioceseQueryFormModel;


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
class DioceseController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 25;


    /**
     * @Route("/query-dioceses", name="query_dioceses")
     */
    public function dioceses (Request $request) {

        $query = $request->query;
        $offset = $query->get('offset') ?? 0;
        $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;
        $initialletter = $query->get('il') ?? 'A-Z';

        $repository = $this->getDoctrine()
                           ->getRepository(Diocese::class);

        $count = $repository->countByInitalletter($initialletter);

        $dioceses = $repository->findByInitialLetterWithBishopricSeat($initialletter, self::LIST_LIMIT, $offset);


        return $this->render('query_diocese/listresult.html.twig', [
            'dioceses' => $dioceses,
            'offset' => $offset,
            'count' => $count,
            'il' => $initialletter,
            'limit' => self::LIST_LIMIT,
        ]);
    }


    /**
     * @Route("/diocese/{idorname}", name="diocese")
     */
    public function getDiocese($idorname, Request $request) {

        $format = $request->query->get('format');

        if(!is_null($format)) {
            return $this->redirectToRoute('diocese_api', [
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
        $initialletter = $request->query->get('il');

        $dioceserepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        $hassuccessor = false;
        if($offset == 0) {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 2, $offset);
            if(count($dioceses) == 2) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[0] : null;
        } else {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 3, $offset - 1);
            if(count($dioceses) == 3) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[1] : null;
        }

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden.");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'il' => $initialletter,
            'flaglist' => null,
        ]);
    }




}
