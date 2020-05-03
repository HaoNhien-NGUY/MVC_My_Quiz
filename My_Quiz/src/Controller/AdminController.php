<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_index")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function usersAdmin(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()->getRepository(User::class)->findAll();

        $users = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/users/{id}/delete", name="admin_user_delete")
     */
    public function deleteUserAdmin(User $user, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('Delete', SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/verify.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/{id}/setadmin", name="admin_user_admin")
     */
    public function setAdminUserAdmin(User $user, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('Mod_him', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setRoles(['ROLE_ADMIN']);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/{id}/verify", name="admin_user_verify")
     */
    public function verifyUserAdmin(User $user, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('Verify', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setIsVerified(true);
            $em->flush();

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users/create", name="admin_user_create");
     */
    public function createUserAdmin(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash(
                'notice',
                'User created'
            );

            $verifToken = md5(random_bytes(5));
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
                ->setVerifToken($verifToken)
                ->setIsVerified(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }
        return $this->render('admin/users/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/quizz", name="admin_quizz")
     */
    public function quizzAdmin(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()->getRepository(Categorie::class)->findAll();

        $quizz = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/quizz.html.twig', [
            'quizz' => $quizz
        ]);
    }

    /**
     * @Route("/admin/quizz/{id}/delete", name="admin_quiz_delete")
     */
    public function deleteQuizAdmin(Categorie $quiz, Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('Delete', SubmitType::class, [
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($quiz);
            $em->flush();

            return $this->redirectToRoute('admin_quizz');
        }

        return $this->render('admin/quizz/delete.html.twig', [
            'quiz' => $quiz,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/stats", name="admin_stats")
     */
    public function statsAdmin()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
