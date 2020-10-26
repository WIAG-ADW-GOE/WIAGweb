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
    const LIST_LIMIT = 20;


    /**
     * @Route("/query-dioceses", name="query_dioceses")
     */
    public function launch_query(Request $request) {

        $diocesequery = new DioceseQueryFormModel;
        $form = $this->createForm(DioceseQueryFormType::class, $diocesequery);


        if ($form->isSubmitted() && $form->isValid()) {

            $diocesequery = $form->getData();
            $someid = $diocesequery->someid;

            if($someid && Person::isWiagidLong($someid)) {
                $diocesequery->someid = Person::wiagidLongToWiagid($someid);
            }

            // get the number of results (without page limit restriction)
            $count = $this->getDoctrine()
                          ->getRepository(Person::class)
                          ->countByQueryObject($diocesequery)[1];

            $page = 0;
            $persons = null;

            if($count > 0) {
                if($form->get('searchJSON')->isClicked()) {
                    $persons = $this->getDoctrine()
                                    ->getRepository(Person::class)
                                    ->findWithOffices($diocesequery);

                    $personExports = array();
                    foreach($persons as $p) {
                        $personExports[] = $p->toJSON();
                    }

                    return $this->json(array('persons' => $personExports));
                }


                $page = $request->request->get('page') ?? 1;

                $personRepository = $this->getDoctrine()
                                         ->getRepository(Person::class);

                $persons = $personRepository->findWithOffices($diocesequery, self::LIST_LIMIT, $page);

                foreach($persons as $p) {
                    if($p->hasMonastery()) {
                        $personRepository->addMonasteryPlaces($p);
                    }
                }
            }

            // combination of POST_SET_DATA and POST_SUBMIT
            // $form = $this->createForm(DioceseQueryFormType::class, $diocesequery);

            return $this->render('query_diocese/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'page' => $page,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
            ]);

        } else {
            return $this->render('query_diocese/launch_query.html.twig', [
                'query_form' => $form->createView(),
            ]);
        }
    }


    /**
     * @Route("/diocese/{wiagid}", name="diocese")
     */
    public function getdiocese($wiagid, Request $request) {

        $format = $request->query->get('format');

        if(!is_null($format)) {
            return $this->redirectToRoute('diocese_api', [
                'wiagid' => $wiagid,
                'format' => $format,
            ]);
        }

        $flaglist = $request->query->get('flaglist');


        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findOneWithOffices($id);

        if (!$diocese) {
            $this->createNotFoundException('Bistum wurde nicht gefunden');
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'flaglist' => $flaglist,
        ]);
    }



}
