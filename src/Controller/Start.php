<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Start extends AbstractController {
    /**
     * @Route("/", name="welcome")
     */
	public function welcome() {
        return $this->render('start/welcome.html.twig');
    }

    /**
     * @Route("/about", name="about_wiag")
     */
	public function about() {
        return $this->render('start/about.html.twig');
    }

    
}
