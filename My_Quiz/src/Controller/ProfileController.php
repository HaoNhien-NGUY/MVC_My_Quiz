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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Mime\Email;

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
    public function edit(MailerInterface $mailer, Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthAuthenticator $authenticator)
    {
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user);
        $oldemail = $user->getEmail();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->get('plainPassword')->isEmpty()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            if ($oldemail != $form->get('email')->getData()) {
                $verifToken = md5(random_bytes(5));
                $user->setIsVerified(false)
                    ->setVerifToken($verifToken);

                //send a verif email
                $email = (new Email())
                    ->from('haquiz@mail.com')
                    ->to($user->getEmail())
                    ->subject('Verify your HAQuiz account')
                    ->text('Please verify your account by clicking with this link : http://localhost:8000/validation/' . $user->getEmail() . '/' . $verifToken);

                $mailer->send($email);
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
