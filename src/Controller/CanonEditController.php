<?php
namespace App\Controller;

use App\Entity\Person;
use App\Entity\CanonGS;
use App\Entity\Canon;
use App\Entity\CnOffice;
use App\Entity\CnNamelookup;
use App\Entity\CnOfficelookup;
use App\Repository\CanonRepository;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;
use App\Form\CanonEditFormType;
use App\Form\CanonEditSearchFormType;
use App\Form\CnOfficeEditFormType;
use App\Form\Model\CanonEditSearchFormModel;
use App\Service\ParseDates;
use App\Service\CnUpdateLookup;
use App\Service\CnOfficeService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_DATA_ADMIN")
 */
class CanonEditController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 20;
    const HINT_LIST_LIMIT = 12;

    /**
     * @Route("/domherren/editlist", name="canon_editlist")
     */
    public function launch_query(Request $request) {

        $querydata = new CanonEditSearchFormModel;

        $form = $this->createForm(CanonEditSearchFormType::class, $querydata);

        $form->handlerequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $repository = $this->getDoctrine()
                               ->getRepository(Canon::class);

            $querydata = $form->getData();

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getCanonInQuery($form, $singleoffset);
            }


            // get the number of results (without page limit restriction)
            $count = $repository->countByQueryObject($querydata)[1];

            // return HTML

            $offset = $request->request->get('offset') ?? 0;

            // extra check to avoid empty lists
            if($count < self::LIST_LIMIT) $offset = 0;

            $offset = (int) floor($offset / self::LIST_LIMIT) * self::LIST_LIMIT;

            $persons = $repository->findByQueryObject($querydata, self::LIST_LIMIT, $offset);
            $filterStatusOn = !is_null($querydata->filterStatus) && count($querydata->filterStatus) > 0;

            return $this->render('canon_edit/list.html.twig', [
                'query_form' => $form->createView(),
                'filterStatusOn' => $filterStatusOn,
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'persons' => $persons,
            ]);

        } else {
            // show empty form only
            return $this->render('canon_edit/launch_query.html.twig', [
                'query_form' => $form->createView(),
                'filterStatusOn' => false,
            ]);
        }
    }

    public function getCanonInQuery($form, $offset) {

        $queryformdata = $form->getData();

        $personRepository = $this->getDoctrine()
                                 ->getRepository(Canon::class);
        $hassuccessor = false;
        if($offset == 0) {
            $persons = $personRepository->findByQueryObject($queryformdata, 2, $offset);
            $iterator = $persons->getIterator();
            if(count($iterator) == 2) $hassuccessor = true;

        } else {
            $persons = $personRepository->findByQueryObject($queryformdata, 3, $offset - 1);
            $iterator = $persons->getIterator();
            if(count($iterator) == 3) $hassuccessor = true;
            $iterator->next();
        }
        $person = $iterator->current();

        $dioceseRepository = $this->getDoctrine()->getRepository(Diocese::class);

        return $this->render('canon_edit/details.html.twig', [
            'query_form' => $form->createView(),
            'person' => $person,
            'wiagidlong' => $person->getId(),
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'dioceserepository' => $dioceseRepository,
        ]);

    }

    /**
     * @Route("/domherren/new", name="canon_new")
     * @IsGranted("ROLE_DATA_ADMIN")
     */
    public function new(EntityManagerInterface $em,
                        Request $request,
                        CnUpdateLookup $update) {
        $form = $this->createForm(CanonEditFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $canon = $form->getData();
            $status = $canon->getStatus();
            if ($status == 'online') {
                $update->setOnline($canon);
            } else {
                # TODO
                $this->unsetOnline($canon, $update);
            }

            $em->persist($canon);
            $em->flush();

            // $this->addFlash('success', 'Domherr angelegt!');
            // TODO
            // update GS info if present

            $id = $canon->getId();
            return $this->redirectToRoute('canon_edit', [
                'id' => $id,
            ]);
        }

        return $this->render('canon_edit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/domherren/edit/{id}", name="canon_edit")
     * @IsGranted("ROLE_DATA_ADMIN")
     */
    public function edit(Canon $canon,
                         EntityManagerInterface $em,
                         Request $request,
                         CnUpdateLookup $update) {
        $form = $this->createForm(CanonEditFormType::class, $canon);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $status = $canon->getStatus();
            if ($status == 'online') {
                $update->setOnline($canon);
            } else {
                # TODO
                $this->unsetOnline($canon, $update);
            }


            $em->persist($canon);
            $em->flush();

            return $this->redirectToRoute('canon_edit', [
                'id' => $canon->getId(),
            ]);
        }

        return $this->render('canon_edit/edit.html.twig', [
            'form' => $form->createView(),
            'canon' => $canon,
        ]);
    }


    /** @Route("/domherren/new-office/{id}", name="canon_new_office")
     * @IsGranted("ROLE_DATA_ADMIN")
     */
    public function new_office(Canon $canon,
                               EntityManagerInterface $em,
                               ParseDates $pd,
                               CnOfficeService $os,
                               Request $request) {
        $form = $this->createForm(CnOfficeEditFormType::class);

        # dump($canon);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $office = $form->getData();

            $numdatestart = $pd->parse($office->getDateStart(), 'lower');
            $office->setNumdateStart($numdatestart);
            $numdateend = $pd->parse($office->getDateEnd(), 'upper');
            $office->setNumdateEnd($numdateend);
            $office->setCanon($canon);
            $os->fillMonastery($office, $office->getFormMonasteryName());
            $os->fillLocationShow($office);

            $id = $canon->getId();

            $em->persist($office);

            if ($office->getComment() == "create brother") {
                $ob = new CnOffice();
                $ob->setOfficeName("B1");
                $os->fillMonastery($ob, "Franziskanerkloster Zerbst");
                $em->persist($ob);
            }
            $em->flush();

            return $this->redirectToRoute('canon_new_office', [
                'id' => $id,
            ]);
        }

        return $this->render('canon_edit_office/new.html.twig', [
            'form' => $form->createView(),
            'canon' => $canon,
        ]);
    }

    /**
     * @Route("/domherren/edit-office/{id}/{idoffice}", name="canon_edit_office")
     * @ParamConverter("canon", options={"id": "id"})
     * @ParamConverter("office", options={"id": "idoffice"})
     * @IsGranted("ROLE_DATA_ADMIN")
     */
    public function edit_office(Canon $canon,
                                CnOffice $office,
                                EntityManagerInterface $em,
                                ParseDates $pd,
                                CnOfficeService $os,
                                Request $request) {

        if (!is_null($office->getMonastery())) {
            $office->setFormMonasteryName($office->getMonastery()->getMonasteryName());
        }
        $form = $this->createForm(CnOfficeEditFormType::class, $office);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $numdatestart = $pd->parse($office->getDateStart(), 'lower');
            $office->setNumdateStart($numdatestart);
            $numdateend = $pd->parse($office->getDateEnd(), 'upper');
            $office->setNumdateEnd($numdateend);

            $id = $canon->getId();
            $office->setCanon($canon);
            $os->fillMonastery($office, $office->getFormMonasteryName());
            $os->fillLocationShow($office);

            $em->persist($office);
            $em->flush();

            return $this->redirectToRoute('canon_new_office', [
                'id' => $id,
            ]);
        }

        // pass `id_edit_office` to skip it in the table of offices
        return $this->render('canon_edit_office/new.html.twig', [
            'form' => $form->createView(),
            'canon' => $canon,
            'id_edit_office' => $office->getId(),
        ]);
    }


    public function unsetOnline($canon, CnUpdateLookup $update) {

    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/name", name="canon_edit_autocomplete_name")
     */
    public function autocompletename(Request $request) {
        $qresult = $this->getDoctrine()
                        ->getRepository(Canon::class)
                        ->suggestName($request->query->get('query'),
                                      self::HINT_LIST_LIMIT);

        return $this->json([
            'choices' => $qresult,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/domstift", name="canon_edit_autocomplete_domstift")
     */
    public function autocompletedomstift(Request $request) {
        $query = trim($request->query->get('query'));
        # strip 'bistum' or 'erzbistum'
        foreach(['Stift', 'Domstift'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs)) {
                $query = trim(str_replace($bs, "", $query));
                break;
            }
        }

        $monasteries = $this->getDoctrine()
                            ->getRepository(Monastery::class)
                            ->suggestDomstift($query, self::HINT_LIST_LIMIT);
        return $this->json([
            'monasteries' => $monasteries,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/monastery", name="canon_edit_autocomplete_monastery")
     */
    public function autocompletemonastery(Request $request) {
        $query = trim($request->query->get('query'));

        $monasteries = $this->getDoctrine()
                            ->getRepository(Monastery::class)
                            ->suggestMonastery($query, self::HINT_LIST_LIMIT);
        return $this->json([
            'choices' => $monasteries,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/episcid", name="canon_edit_autocomplete_episcid")
     */
    public function autocompleteepiscid(Request $request) {
        $query = $request->query->get('query');
        $id = Person::extractDbId($query);
        $id = $id ?? $query;


        $suggestions = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->suggestId($id, self::HINT_LIST_LIMIT);

        foreach ($suggestions as $k => $v) {
            $vv = $v['suggestion'];
            $suggestions[$k] = [
                'suggestion' => Person::decorateId($vv),
            ];
        }

        return $this->json([
            'choices' => $suggestions,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/place", name="canon_edit_autocomplete_place")
     */
    public function autocompleteplace(Request $request) {
        $query = trim($request->query->get('query'));
        foreach(['Stift', 'Domstift'] as $bs) {
            if(!is_null($query) && str_starts_with($query, $bs)) {
                $query = trim(str_replace($bs, "", $query));
                break;
            }
        }

        $places = $this->getDoctrine()
                       ->getRepository(CnOffice::class)
                       ->suggestPlace($query, self::HINT_LIST_LIMIT);
        return $this->json([
            'places' => $places,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/office", name="canon_edit_autocomplete_office")
     */
    public function autocompleteoffice(Request $request) {
        $offices = $this->getDoctrine()
                        ->getRepository(CnOffice::class)
                        ->suggestOffice($request->query->get('query'),
                                        self::HINT_LIST_LIMIT);

        return $this->json([
            'offices' => $offices,
        ]);
    }

    /**
     * AJAX callback
     * @Route("domherren/edit/autocomplete/gsn", name="canon_edit_autocomplete_gsn")
     */
    public function autocompletegsn(Request $request) {
        $qresult = $this->getDoctrine()
                        ->getRepository(CanonGS::class)
                        ->suggestGsn($request->query->get('query'),
                                     self::HINT_LIST_LIMIT);

        return $this->json([
            'choices' => $qresult,
        ]);
    }

}
