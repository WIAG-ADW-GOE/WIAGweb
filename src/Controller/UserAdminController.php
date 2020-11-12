<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Entity\User;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class UserAdminController extends AbstractController {
    /**
     * Parameters
     */

    /**
     * @Route("/admin/add-user", name="admin_add_user")
     */
    public function addUser(Request $request) {

        $form = $this->createForm(UserFormType::class);

        $form->handlerequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();
            dump($user);

            # save user data
            # clear passwordTwin

            return $this->render('admin/add_user.html.twig', [
                'form' => $form->createView()
            ]);

        } else {
            return $this->render('admin/add_user.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }
}
