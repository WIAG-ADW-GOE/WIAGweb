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
     *
     * @return Response                 HTML
     *
     * @Route("/api/domstift/domherren", name="api_query_domstift_canons")
     */
    public function groupCanonsByOffice(Request $request) {

        $monasteryName = $request->query->get('monastery');
        if (is_null($monasteryName)) {
            $monasteryName = $request->query->get('domstift');
        }

        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        $officeNames = null;
        $repository = null;

        // find offices
        $officeNames = $this->getDoctrine()
                            ->getRepository(CnOfficeDesignation::class)
                            ->findByMonastery($monasteryName, $limit, $offset);

        $repository = $this->getDoctrine()->getRepository(CnOnline::class);
        $canonRepository = $this->getDoctrine()->getRepository(Canon::class);

        return $this->render('canon/printlist.html.twig', [
            'monasteryName' => $monasteryName,
            'officeNames' => $officeNames,
            'repository' => $repository,
            'canonRepository' => $canonRepository,
            'limit' => $limit,
        ]);

    }


}
