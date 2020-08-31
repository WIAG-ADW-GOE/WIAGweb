<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Service\DataBaseInteraction;

use Ds\Set;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class QueryBishop extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 30;

    
	/**
     * @Route("/query-bishops", name="launch_query")
     */
    public function launch_query(Request $request, DataBaseInteraction $dbio) {
        
        $form = $this->createForm(BishopQueryFormType::class);
                
        $form->handlerequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $data = $form->getData();
            $bishopquery = new BishopQueryFormModel($data['name'], $data['place'], $data['year'], $data['someid']);
            
            // dd($bishopquery);
            // dd($data);            

            $count = $this->getDoctrine()
                            ->getRepository(Person::class)
                            ->countByQueryObject($bishopquery)[0]['count'];

            $page = $request->request->get('page');
            if (!$page) {
                $page = 1;
            }

            $persons = $dbio->findPersonsAndOffices($bishopquery, self::LIST_LIMIT, $page);


            return $this->render('query_bishop/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'limit' => self::LIST_LIMIT,
                'page' => $page,
                'persons' => $persons,
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
        dd($person, self::LIST_LIMIT);
    }


    
}

    
