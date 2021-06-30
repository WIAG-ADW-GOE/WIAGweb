<?php

namespace App\Controller;

use App\Entity\CnReference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CanonReferenceController extends AbstractController {

    /**
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
     * @Route("/canon-reference/list", name="canon_reference_list");
     */
    public function list() {

        $references = $this->getDoctrine()
                           ->getRepository(CnReference::class)
                           ->findAll();

        return $this->render("canon_reference/list.html.twig", [
            'references' => $references,
        ]);
    }

    /**
     * @Route("/canon-reference/edit", name="canon_reference_editlist");
     */
    public function editlist() {
        $references = $this->getDoctrine()
                           ->getRepository(CnReference::class)
                           ->findAll();

        return $this->render("canon_reference/list.html.twig", [
            'references' => $references,
            'link_path' => 'canon_reference_edit',
        ]);
    }

    /**
     * @Route("/canon-reference/edit/{id}", name="canon_reference_edit");
     */
    public function edit(CnReference $reference) {

        return $this->render("canon_reference/edit.html.twig", [
            'reference' => $reference
        ]);

    }

}
