<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Entity\Person;
use App\Entity\Office;

use Ds\Vector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class QueryBishop extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 30;

    
	/**
     * @Route("/query-bishops", name="launch_query")
     */
    public function launch_query(Request $request) {
        $form = $this->createForm(BishopQueryFormType::class);
        $form->handlerequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BishopQueryFormModel $bishopquery
             */
            $bishopquery = $form->getData();

            $count = $this->getDoctrine()
                            ->getRepository(Person::class)
                            ->countByQueryObject($bishopquery)[0]['count'];

            $page = $request->request->get('page');            
            if (!$page) {
                $page = 1;
            }
            
            $persons = $this->getDoctrine()
                            ->getRepository(Person::class)
                            ->findByQueryObject($bishopquery, self::LIST_LIMIT, $page);


            // dd($persons[11]['familyname']);

            // add offices

            $officeRepository = $this->getDoctrine()
                                     ->getRepository(Office::class);
            $rawoffices = array();
            $officetexts = new Vector();
            $displaypersons = new Vector;

            foreach ($persons as $person) {
                $officetexts->clear();
                $rawoffices = $officeRepository->findByIDPerson($person['wiagid']);
                foreach ($rawoffices as $o) {
                    $officetexts->push($o['office_name'].' ('.$o['diocese'].')');
                }
                $person['offices'] = $officetexts->join(', ');
                $displaypersons->push($person);
            }

            return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'page' => $page,
                'persons' => $displaypersons,
            ]);

        } else {
            return $this->render('query_bishop/launch_query.html.twig', [
                'query_form' => $form->createView(),
            ]);
        }
    }

   
    /**
     * @Route("/bishop/{id}", name="bishop")
     */
    public function getperson($id) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                        ->findOneByWiagid($id);

        $offices = $this->getDoctrine()
                        ->getRepository(Office::class)
                        ->findByIDPerson($person->getWiagid());

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'offices' => $offices,
        ]);
    }


    /**
     * @Route("/query-test/{id}")
     */
    public function queryid($id) {
        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneByWiagid($id);
        dd($person, $this->size_list);
    }
}

// Example - $qb->andWhere($qb->expr()->orX($qb->expr()->lte('u.age', 40), 'u.numChild = 0'))
    
