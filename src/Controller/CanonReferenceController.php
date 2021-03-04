<?php

namespace App\Controller;

use App\Entity\CnReference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CanonReferenceController extends AbstractController {

    /**
     * @Route("/canon-reference/{id}", name="canon_reference");
     */
    public function detailsByShort($id) {

        $reference = $this->getDoctrine()
                          ->getRepository(CnReference::class)
                          ->find($id);
        if (!$reference) {
            throw $this->createNotFoundException('Dieses Referenzwerk wurde nicht gefunden.');
        }
        return $this->render("canon_reference/details.html.twig", [
            'reference' => $reference,
        ]);
    }

    /**
     * @Route("/canon-references", name="canon_reference_list");
     */
    public function list() {

        $references = $this->getDoctrine()
                           ->getRepository(CnReference::class)
                           ->findAll();

        return $this->render("canon_reference/list.html.twig", [
            'references' => $references,
        ]);
    }

}
