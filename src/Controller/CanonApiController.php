<?php

namespace App\Controller;

use App\Entity\CnOnline;
use App\Entity\Canon;
use App\Entity\Domstift;
use App\Entity\CnOfficeDesignation;
// use App\Entity\CanonsByOffice;
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
 * hande API request
 *
 * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
 */
class CanonApiController extends AbstractController {

    const LIST_LIMIT = 20;

    /**
     * accept query request, display list of matching canons
     *
     * @return Response                 HTML
     *
     * @Route("/api/domherren", name="api_query_canons")
     */
    public function listcanons(Request $request) {

        $format = $request->query->get('format') ?? 'html';

        if(array_search($format, ['html']) === false) {
            // TODO set up error pages
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        $data = $request->query->all();
        dump($data);

        $model = new CanonFormModel();
        $model->setFieldsByArray($data);

        $repository = $this->getDoctrine()->getRepository(CnOnline::class);
        // dd($queryformdata);
        $persons = $repository->findByQueryObject($model);

        dump($persons);

        foreach($persons as $p) {
            /* It may look strange to do queries in a loop, but we have two data sources.
               The list is not long (LIST_LIMIT).
            */
            $repository->fillListData($p);
        }

        return $this->render('canon/printlist.html.twig', [
            'persons' => $persons,
            'count' => count($persons),
            'offset' => 0,
        ]);

    }

    /**
     *
     * @return Response                 HTML
     *
     * @Route("/api/domstift/domherren", name="api_query_domstift_canons")
     */
    public function groupCanonsByOffice(Request $request) {

        $monasteryName = $request->query->get('monastery');

        $officeNames = null;
        $repository = null;


        // find offices
        $officeNames = $this->getDoctrine()
                            ->getRepository(CnOfficeDesignation::class)
                            ->findByMonastery($monasteryName);


        $repository = $this->getDoctrine()->getRepository(CnOnline::class);
        $canonRepository = $this->getDoctrine()->getRepository(Canon::class);

        return $this->render('canon/printlist.html.twig', [
            'monasteryName' => $monasteryName,
            'officeNames' => $officeNames,
            'repository' => $repository,
            'canonRepository' => $canonRepository,
        ]);

    }


}
