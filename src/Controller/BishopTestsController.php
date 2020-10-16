<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_USER")
 */
class BishopTestsController extends AbstractController {

    /**
     * @Route("/query-bishops/tests", name="bishop_tests")
     */
    public function list() {

        return $this->render('query_bishop/tests.html.twig');
    }
}
