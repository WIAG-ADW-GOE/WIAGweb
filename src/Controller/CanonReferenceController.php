<?php

namespace App\Controller;

use App\Entity\CnReference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * edit canon references
 */
class CanonReferenceController extends AbstractController {

    /**
     * show details for a reference for canons
     *
     * @return Response                 HTML
     *
     * @Route("/canon-reference/id/{id}", name="canon_reference");
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
     * show list of references
     *
     * @Route("/canon-reference/list", name="canon_reference_list");
     */
    public function list() {
        $references = $this->getDoctrine()
                           ->getRepository(CnReference::class)
                           ->findAll();

        return $this->render("canon_reference/list.html.twig", [
            'references' => $references,
            'link_path' => 'canon_reference_edit',
        ]);
    }

    /**
     * show edit form for a reference for canons
     *
     * [work in progress]
     *
     * @todo edit canon references
     *
     * @Route("/canon-reference/edit/{id}", name="canon_reference_edit");
     */
    public function edit(CnReference $reference) {

        return $this->render("canon_reference/edit.html.twig", [
            'reference' => $reference
        ]);

    }

}
