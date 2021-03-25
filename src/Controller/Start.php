<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class Start extends AbstractController {
    /**
     * @Route("/", name="wiag_welcome")
     */
    public function welcome() {
        return $this->render('start/welcome.html.twig');
    }

    /**
     * @Route("/about", name="wiag_about")
     */
    public function about() {
        return $this->render('start/about.html.twig');
    }

    /**
     * @Route("/api-doc", name="api_documentation")
     */
    public function apidoc() {
        return $this->render('start/apidoc.html.twig');
    }

    /**
     * @Route("/contact", name="wiag_contact")
     */
    public function contact() {
        return $this->render('start/contact.html.twig');
    }

    /**
     * @Route("/phpinfo", name="wiag_phpinfo")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show_phpinfo() {
        phpinfo();
    }

    /**
     * @Route("/about/images", name="about_images")
     */
    public function images() {
        return $this->render('start/images.html.twig');
    }

    /**
     * @Route("/about/data-service", name="data_service")
     */
    public function dataService() {
        return $this->render('start/data_service.html.twig');
    }    


}
