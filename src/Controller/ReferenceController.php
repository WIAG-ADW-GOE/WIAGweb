<?php

namespace App\Controller;

use App\Entity\Reference;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReferenceController extends AbstractController {

    /**
     * @Route("/reference/{short}", name="reference");
     */
    public function detailsByShort($short) {

        $reference = $this->getDoctrine()
                          ->getRepository(Reference::class)
                          ->findOneByBibshort($short);
        return $this->render("reference/details.html.twig", [
            'reference' => $reference,
        ]);
    }
}
