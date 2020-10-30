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

        $page = $request->query->get('page') ?? 1;

        $repository = $this->getDoctrine()
                           ->getRepository(Diocese::class);

        $count = $repository->count([]);

        $dioceses = $repository->findAllWithBishopricSeat($page, self::LIST_LIMIT);


        return $this->render('query_diocese/listresult.html.twig', [
            'dioceses' => $dioceses,
            'page' => $page,
            'count' => $count,
            'limit' => self::LIST_LIMIT,
        ]);
    }


    /**
     * @Route("/diocese/{idorname}", name="diocese")
     */
    public function getdiocese($idorname, Request $request) {

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
            throw $this->createNotFoundException("Bistum wurde nicht gefunden: {$id}");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'flaglist' => $flaglist,
        ]);
    }



}
