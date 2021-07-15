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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * manage users
 *
 * @IsGranted("ROLE_ADMIN")
 */
class UserAdminController extends AbstractController {

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder) {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * create user credentials
     *
     * @Route("/admin/add-user", name="admin_add_user")
     */
    public function addUser(Request $request, EntityManagerInterface $entityManager) {

        $form = $this->createForm(UserFormType::class);

        $form->handlerequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $pwd = $user->getPassword();
            $user->setPassword($this->passwordEncoder->encodePassword($user, $pwd));
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('admin_add_user_success');
        } else {
            return $this->render('admin/add_user.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    /**
     * show message
     *
     * @Route("/admin/add-user-success", name="admin_add_user_success")
     */
    public function addUserSucces () {
        return $this->render('admin/message.html.twig', [
            'message' => 'User ist eingetragen',
        ]);
    }

}
