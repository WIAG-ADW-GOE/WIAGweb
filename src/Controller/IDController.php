<?php

namespace App\Controller;

use App\Form\BishopQueryFormType;
use App\Form\Model\BishopQueryFormModel;
use App\Entity\Person;
use App\Entity\Office;
use App\Entity\Officedate;
use App\Entity\Monastery;
use App\Entity\MonasteryLocation;
use App\Entity\Diocese;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

/**
 * @IsGranted("ROLE_QUERY")
 */
class IDController extends AbstractController {

    /** 
     * @Route("/id/{id}", name="id")
     */
    public function redirectID(string $id, Request $request) {
        /* TODO check MIME type; default: HTML */
        if(preg_match("/WIAG-Pers-EPISCGatz-.+/", $id))
            return $this->redirectToRoute('doc_bishop', ['id' => $id], 303);
        elseif(preg_match("/WIAG-Inst-DIOCGatz-.+/", $id))
           return $this->redirectToRoute('doc_diocese', ['id' => $id], 303);
        else
            throw $this->createNotFoundException('Keine Objekte für dieses ID-Muster');
    }

    /**
     * @Route("/doc/{id}", name="doc_bishop", requirements={"id"="WIAG-Pers-EPISCGatz-.+"})
     */
    public function bishop(string $id, Request $request) {

        $idindb = Person::wiagidLongToWiagid($id);

        $person = $this->getDoctrine()
                       ->getRepository(Person::class)
                       ->findOneWithOffices($idindb);

        if (!$person) {
            throw $this->createNotFoundException('Person wurde nicht gefunden');
        }

        $dioceseRepository = $this->getDoctrine()
                                  ->getRepository(Diocese::class);

        return $this->render('query_bishop/details.html.twig', [
            'person' => $person,
            'wiagidlong' => $id,
            'querystr' => null,
            'dioceserepository' => $dioceseRepository,
        ]);
    }

    /**
     * @Route("/doc/{id}", name="doc_diocese", requirements={"id"="WIAG-Inst-DIOCGatz-.+"})
     */
    public function diocese(string $id, Request $request) {
        
        $diocese = $this->getDoctrine()
                        ->getRepository(Diocese::class)
                        ->findWithBishopricSeat($id);

        if (!$diocese) {
            throw $this->createNotFoundException("Bistum wurde nicht gefunden: {$id}");
        }


        return $this->render('query_diocese/details.html.twig', [
            'diocese' => $diocese,
        ]);
    }

    
}
