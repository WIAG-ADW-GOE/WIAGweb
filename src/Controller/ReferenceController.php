<?php

namespace App\Controller;

use App\Entity\Reference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReferenceController extends AbstractController {

    /**
     * @Route("/reference/{shortenc}", name="reference");
     */
    public function detailsByShort($shortenc) {

        $short = urldecode($shortenc);
        $reference = $this->getDoctrine()
                          ->getRepository(Reference::class)
                          ->findOneByBibshort($short);
        if (!$reference) {
            throw $this->createNotFoundException('Dieses Referenzwerk wurde nicht gefunden.');
        }
        return $this->render("reference/details.html.twig", [
            'reference' => $reference,
        ]);
    }

    /**
     * @Route("/reference-list", name="reference_list");
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
