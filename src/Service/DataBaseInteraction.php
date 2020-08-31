<?php

namespace App\Service;

use App\Entity\Person;
use App\Entity\Office;
use App\Form\Model\BishopQueryFormModel;

use Ds\Vector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DataBaseInteraction extends AbstractController {

    public function findPersonsAndOffices(BishopQueryFormModel $bishopquery, $limit, $page) {
        $persons = $this->getDoctrine()
                        ->getRepository(Person::class)
                        ->findByQueryObject($bishopquery, $limit, $page);
        

        // add offices
        $officeRepository = $this->getDoctrine()
                                 ->getRepository(Office::class);
        $rawoffices = array();
        $officetexts = new Vector();
        $persons_with_offices = new Vector;
        
        foreach ($persons as $person) {
            $officetexts->clear();
            $rawoffices = $officeRepository->findByIDPerson($person['wiagid']);
            foreach ($rawoffices as $o) {
                $officetexts->push($o['office_name'].' ('.$o['diocese'].')');
            }
            $person['offices'] = $officetexts->join(', ');
            $persons_with_offices->push($person);
        }

        return $persons_with_offices;
    }
	
}
