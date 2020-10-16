<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
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
class BishopApiController extends AbstractController {

    /**
     * @Route("/api/bishop/{wiagidlong}", name="api_bishop")
     */
    public function getperson($wiagidlong, Request $request) {

        $format = $request->query->get('format');

        if($format != 'json') {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        // remove prefix and suffix
        $id = Person::wiagidLongToWiagid($wiagidlong);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($id);

        if (!$person) {
            $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $personExport = $person->toJSON();
        return $this->json($personExport);

    }


    /**
     * @Route("/api/query-bishops", name="api_query_bishops")
     */
    public function apigetpersons(Request $request) {

        #dd($request);

        $name = $request->query->get('name');
        $place = $request->query->get('diocese');
        $office = $request->query->get('office');
        $year = $request->query->get('year');
        $someid = $request->query->get('someid');

        if($someid && Person::isWiagidLong($someid)) {
            $someid = Person::wiagidLongToWiagid($someid);
        }

        $bishopquery = new BishopQueryFormModel($name,
                                                $place,
                                                $office,
                                                $year,
                                                $someid,
                                                array(),
                                                array());

        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findWithOffices($bishopquery);

        # dump($persons);

        $personExports = array();
        foreach($persons as $p) {
            $personExports[] = $p->toJSON();
        }

        return $this->json(array(
            'count' => count($persons),
            'persons' => $personExports)
        );
        // return $this->render('start/welcome.html.twig');
    }

}
