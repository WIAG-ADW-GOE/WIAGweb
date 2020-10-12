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
     * Parameters
     */
    const LIST_LIMIT = 30;
    const WIAGID_PREFIX = 'WIAG-Pers-EPISCGatz-';
    const WIAGID_POSTFIX = '-001';


    /**
     * @Route("/bishop-api/{wiagidlong}", name="bishop_api")
     */
    public function getperson($wiagidlong, Request $request) {

        $format = $request->query->get('format');

        if($format != 'json') {
            throw $this->createNotFoundException('Unbekanntes Format: '.$format.'.');
        }

        // remove prefix and suffix
        $pos = strlen(self::WIAGID_PREFIX);
        $id = substr($wiagidlong, $pos, -4);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOnePersonAndOffices($id);

        if (!$person) {
            $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $personExport = $this->filter_person($person);
        return $this->json($personExport);

    }

    /**
     * @Route("/api/query-bishops", name="api_query_bishops")
     */
    public function getpersons(Request $request) {

        $name = $request->query->get('name');
        $place = $request->query->get('place');
        $office = $request->query->get('office');
        $year = $request->query->get('year');
        $someid = $request->query->get('someid');

        // ## TODO Facetten auslesen
        $bishopquery = new BishopQueryFormModel($name,
                                                $place,
                                                $office,
                                                $year,
                                                $someid,
                                                array(),
                                                array());

        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findObjectsByQueryObject($bishopquery);

        dump($persons);

        $personExports = array();
        foreach($persons as $p) {
            $personExports[] = $this->filter_person($p);
        }

        return $this->json($personExports);
        // return $this->render('start/welcome.html.twig');
    }

    public function filter_person(Person $person) {
        $pj = array();
        $pj['wiagId'] = $person->getWiagid();

        $fv = $person->getFamilyname();
        if($fv) $pj['familyName'] = $fv;

        $pj['givenName'] = $person->getGivenname();

        $fv = $person->getPrefixName();
        if($fv) $pj['prefix'] = $fv;

        $fv = $person->getFamilynameVariant();
        if($fv) $pj['variantFamilyName'] = $fv;

        $fv = $person->getGivennameVariant();
        if($fv) $pj['variantGivenName'] = $fv;

        $fv = $person->getDateBirth();
        if($fv) $pj['dateOfBirth'] = $fv;

        $fv = $person->getDateDeath();
        if($fv) $pj['dateOfDeath'] = $fv;

        $fv = $person->getReligiousOrder();
        if($fv) $pj['religiousOrder'] = $fv;

        if($person->hasNormdata()) {
            $pj['normdata'] = array();
            $nd = &$pj['normdata'];
            $fv = $person->getGsid();
            if($fv) $nd['gsId'] = $fv;

            $fv = $person->getGndid();
            if($fv) $nd['gndId'] = $fv;

            $fv = $person->getViafid();
            if($fv) $nd['viafId'] = $fv;

            $fv = $person->getWikidataid();
            if($fv) $nd['wikidataId'] = $fv;

            $fv = $person->getWikipediaurl();
            if($fv) $nd['wikipediaUrl'] = $fv;
        }

        $offices = $person->getOffices();
        dump($offices);
        if($offices) {
            $pj['offices'] = $this->filter_offices($offices);
        }

        $fv = $person->getReference();
        if($fv) $pj['reference'] = $fv;

        return $pj;
    }

    public function filter_offices($offices) {
        $ocjs = array();
        foreach($offices as $oc) {
            $ocj = array();

            $ocj['officeTitle'] = $oc->getOfficeName();

            $fv = $oc->getDiocese();
            if($fv) $ocj['diocese'] = $fv;

            $fv = $oc->getDateStart();
            if($fv) $ocj['dateStart'] = $fv;

            $fv = $oc->getDateEnd();
            if($fv) $ocj['dateEnd'] = $fv;

            $fv = $oc->getComment();
            if($fv) $ocj['comment'] = $fv;

            $ocjs[] = $ocj;
        }
        return $ocjs;
    }
}
