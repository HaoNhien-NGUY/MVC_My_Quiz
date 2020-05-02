<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Form\EditProfileType;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthAuthenticator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_index")
     */
    public function index()
    {
        $resultats = $this->getUser()->getResultats();
        $resultats = array_reverse($resultats->toArray());

        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'resultats' => $resultats
        ]);
    }

    /**
     * @Route("/profile/edit", name="profile_edit")
     */
    public function edit(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthAuthenticator $authenticator)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$form->get('plainPassword')->isEmpty()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('profile_index');
        }

        return $this->render('profile/edit.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }
}
