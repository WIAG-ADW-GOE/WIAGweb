<?php

namespace App\Controller;

use App\Entity\Reference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * show references for bishops
 */
class ReferenceController extends AbstractController {

    /**
     * show list of refereces for bishops
     *
     * [2021-07-15; not in navigation menu]
     *
     * @Route("/reference/{id}", name="reference");
     */
    public function detailsByShort($id) {

        $reference = $this->getDoctrine()
                          ->getRepository(Reference::class)
                          ->find($id);
        if (!$reference) {
            throw $this->createNotFoundException('Dieses Referenzwerk wurde nicht gefunden.');
        }
        return $this->render("reference/details.html.twig", [
            'reference' => $reference,
        ]);
    }

    /**
     * show details for a reference for bishops
     *
     * @Route("/references", name="reference_list");
     */
    public function list() {

        $references = $this->getDoctrine()
                           ->getRepository(Reference::class)
                           ->findAll();

        return $this->render("reference/list.html.twig", [
            'references' => $references,
        ]);
    }

}
