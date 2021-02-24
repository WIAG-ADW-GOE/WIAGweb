<?php

namespace App\Controller;

use App\Entity\Domstift;
use App\Entity\Person;
use App\Entity\Diocese;
use App\Service\HTTPClient;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;



/**
 * @IsGranted("ROLE_QUERY")
 */
class CanonGSController extends AbstractController {
    /**
     * Parameters
     */
    const LIST_LIMIT = 25;


    /**
     * @Route("/domherren", name="query_canons")
     */
    public function canons (Request $request, HTTPClient $client) {

        $route_utility_stiftnames = $this->generateUrl('query_canons_utility_stiftnames');

        $domstiftchoices = $this->getDoctrine()
                                ->getRepository(Domstift::class)
                                ->findChoiceList();

        $form = $this->createFormBuilder()
                     ->add('domstift', ChoiceType::class, [
                         'label' => false,
                         'choices' => $domstiftchoices,
                         'attr' => [
                             'type' => 'submit',
                             ]
                     ])
                     ->add('searchHTML', SubmitType::class, [
                         'label' => 'Suche',
                         'attr' => [
                             'class' => 'btn btn-secondary btn-light',
                         ],
                     ])
                     ->getForm();

        $form->handlerequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $choice = $form->getData();
            $domstift_gs_id = $choice['domstift'];

            $singleoffset = $request->request->get('singleoffset');
            if(!is_null($singleoffset)) {
                return $this->getCanonInQuery($form, $singleoffset, $client);
            }


            $offset = $request->request->get('offset') ?? 0;
            $limit = self::LIST_LIMIT;

            $offset = (int) floor($offset / $limit) * $limit;

            $persons = $client->findCanonByDiocese($domstift_gs_id, 500, 0);
            $count = count($persons);

            $persons = $client->findCanonByDiocese($domstift_gs_id, $limit, $offset);


            return $this->render('query_canon/listresult.html.twig', [
                'query_form' => $form->createView(),
                'count' => $count,
                'abovelimit' => $count == 500 ? 'mehr als ' : '',
                'limit' => self::LIST_LIMIT,
                'offset' => $offset,
                'persons' => $persons,
            ]);

        }


        return $this->render('query_canon_gs/launch_query.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    public function getCanonInQuery($form, $offset, HTTPClient $client) {

        $choice = $form->getData();
        $domstift_gs_id = $choice['domstift'];

        $hassuccessor = false;
        $i = 0;
        if($offset == 0) {
            $persons = $client->findCanonByDiocese($domstift_gs_id, 2, $offset);
            if(count($persons) == 2) $hassuccessor = true;
        } else {
            $persons = $client->findCanonByDiocese($domstift_gs_id, 3, $offset - 1);
            if(count($persons) == 3) $hassuccessor = true;
            $i = 1;
        }
        $person = $persons[$i];

        $person->flagcomment = !is_null($person->person->anmerkungen) && $person->person->anmerkungen != "";
        $person->hasExternalIdentifier = (
            !is_null($person->person->gndnummer) && $person->person->gndnummer != ""
            || !is_null($person->person->viaf) && $person->person->viaf != "");

        $gsns = $person->{'item.gsn'};
        $person->gsn_id = $gsns[0]->nummer;

        $dioceserepository = $this->getDoctrine()->getRepository(Diocese::class);

        $personrepository = $this->getDoctrine()->getRepository(Person::class);
        $wiag_person = $personrepository->findOneByGsid($person->gsn_id);

        return $this->render('query_canon_gs/details.html.twig', [
            'query_form' => $form->createView(),
            'person' => $person,
            'wiag_person' => $wiag_person,
            'offset' => $offset,
            'hassuccessor' => $hassuccessor,
            'dioceserepository' => $dioceserepository,
        ]);

    }

}
