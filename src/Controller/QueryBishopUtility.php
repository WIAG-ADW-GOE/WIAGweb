<?php

namespace App\Controller;

use App\Repository\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class QueryBishopUtility extends AbstractController {

    /** 
     *@Route("/query-bishops/utility/names", methods="GET", name="query_bishops_names_utility")
     */
    public function getNamesApi(PersonRepository $personRepository) {
        //$persons = $personRepository->findByFamilyname($request->query->get('query'));
        $persons = $personRepository->findByFamilyname("ege");
        return $this->json([
            'persons' => $persons,
        ]);
    }
}
