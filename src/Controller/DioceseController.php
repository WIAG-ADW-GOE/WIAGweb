<?php

namespace App\Controller;

use App\Entity\Diocese;
# use App\Form\DioceseQueryFormType;
# use App\Form\Model\DioceseQueryFormModel;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
     * list dioceses by initial letter
     * @Route("/list-dioceses", name="list_dioceses")
     */
    public function listDioceses (Request $request) {

        $query = $request->query;
        $offset = $query->get('offset') ?? 0;
        $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;
        $initialletter = $query->get('name') ?? 'A-Z';

        $repository = $this->getDoctrine()
                           ->getRepository(Diocese::class);

        $count = $repository->countByInitalletter($initialletter);

        $dioceses = $repository->findByInitialLetterWithBishopricSeat($initialletter, self::LIST_LIMIT, $offset);


        return $this->render('query_diocese/list.html.twig', [
            'dioceses' => $dioceses,
            'offset' => $offset,
            'count' => $count,
            'name' => $initialletter,
            'limit' => self::LIST_LIMIT,
        ]);
    }

    /**
     * @Route("/query-dioceses", name="query_dioceses")
     */
    public function dioceses (Request $request) {

        $diocesequery = new Diocese();

        $route_utility_names = $this->generateUrl('query_dioceses_utility_names');

        $form = $this->createFormBuilder($diocesequery)
                     ->add('diocese', TextType::class, [
                         'label' => 'Name',
                         'required' => false,
                         'attr' => [
                             'class' => 'js-name-autocomplete',
                             'data-autocomplete-url' => $route_utility_names,
                             'size' => 15,
                         ],
                     ])
                     ->add('searchHTML', SubmitType::class, [
                         'label' => 'Suche',
                     ])
                     ->getForm();

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $diocesequery = $form->getData();

            # strip 'bistum' or 'erzbistum' from search field diocese
            $diocesequery->normDiocese();

            $offset = $request->request->get('offset') ?? 0;

            $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $singleoffset = $request->request->get('singleoffset');

            $name = $diocesequery->getDiocese();

            $repository = $this->getDoctrine()
                               ->getRepository(Diocese::class);


            if(!is_null($singleoffset)) {
                return $this->getDioceseInQuery($form, $singleoffset);
            } else {
                $count = $repository->countByName($name);
                $dioceses = $repository->findByNameWithBishopricSeat($name, self::LIST_LIMIT, $offset);
            }

            return $this->render('query_diocese/listresult.html.twig', [
                'form' => $form->createView(),
                'dioceses' => $dioceses,
                'offset' => $offset,
                'count' => $count,
                'name' => $name,
                'limit' => self::LIST_LIMIT,
            ]);

        }

        return $this->render('query_diocese/launch_query.html.twig', [
            'form' => $form->createView(),
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

    public function getDioceseInQuery($form, $offset) {

        # dd($name, $offset);

        $dioceserepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        $name = $form->getData()->getDiocese();

        $hassuccessor = false;
        if($offset == 0) {
            $dioceses = $dioceserepository->findByNameWithBishopricSeat($name, 2, $offset);
            if(count($dioceses) == 2) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[0] : null;
        } else {
            $dioceses = $dioceserepository->findByNameWithBishopricSeat($name, 3, $offset - 1);
            if(count($dioceses) == 3) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[1] : null;
        }

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden.");
        }

        return $this->render('query_diocese/details.html.twig', [
            'form' => $form->createView(),
            'diocese' => $diocese,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
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
        $initialletter = $request->query->get('name');
        if($initialletter != 'A-Z')
            $initialletter = substr($initialletter, 0, 1);

        $dioceserepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        $hassuccessor = false;
        $nextname = null;
        $previousname = null;
        if($offset == 0) {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 2, $offset);
            if(count($dioceses) == 2) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[0] : null;
            if($hassuccessor) $nextname = $dioceses[1]->getDiocese();
        } else {
            $dioceses = $dioceserepository->findByInitialLetterWithBishopricSeat($initialletter, 3, $offset - 1);
            $previousname = $dioceses ? $dioceses[0]->getDiocese() : null;
            if(count($dioceses) == 3) $hassuccessor = true;
            $diocese = $dioceses ? $dioceses[1] : null;
            if($hassuccessor) $nextname = $dioceses[2]->getDiocese();
        }


        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden.");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'previousname' => $previousname,
            'nextname' => $nextname,
            'name' => $initialletter,
            'flaglist' => null,
        ]);
    }

    /**
     * @Route("/requery-dioceses", name="requery_dioceses")
     */
    public function reloadForm(Request $request) {
        $diocesequery = new Diocese();
        $diocesequery->setByRequest($request);
        // querystr without offset
        $querystr = http_build_query($diocesequery->toArray());
        $form = $this->createForm(DioceseQueryFormType::class, $diocesequery);


        $someid = $diocesequery->someid;

        if($someid && Person::isWiagidLong($someid)) {
            $diocesequery->someid = Person::wiagidLongToWiagid($someid);
        }

        // get the number of results (without page limit restriction)
        $count = $this->getDoctrine()
                      ->getRepository(Person::class)
                      ->countByQueryObject($diocesequery)[1];

        $facetPlacesState = 'show';
        $facetOfficesState = 'show';
        $persons = null;

        if($count > 0) {
            $personRepository = $this->getDoctrine()
                                     ->getRepository(Person::class);
            $offset = $request->query->get('offset') ?? 0;
            # map to pages
            $offset = floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $persons = $personRepository->findWithOffices($diocesequery, self::LIST_LIMIT, $offset);

            foreach($persons as $p) {
                if($p->hasMonastery()) {
                    $personRepository->addMonasteryLocation($p);
                }
            }
        }

        return $this->render('query_diocese/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'querystr' => $querystr,
                'persons' => $persons,
                'facetPlacesState' => $facetPlacesState,
                'facetOfficesState' => $facetOfficesState,
            ]);

    }




}
