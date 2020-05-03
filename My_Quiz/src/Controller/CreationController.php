<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\AddQuestionType;
use App\Form\CreateQuizType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreationController extends AbstractController
{
    /**
     * @Route("/creation", name="creation_index")
     */
    public function index(Request $request, SessionInterface $session)
    {        
        $form = $this->createForm(CreateQuizType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('add_auth', true);
            $slugger = new AsciiSlugger();
            $em = $this->getDoctrine()->getManager();

            $categorie = new Categorie();
            $quizName = $form->get('quiz_name')->getData();
            $slug = $slugger->slug($quizName)->lower();
            $categorie->setName($quizName)
                ->setSlug($slug);

            $question = new Question();
            $question->setQuestion($form->get('question')->getData());

            for ($i = 1; $i <= 3; $i++) {
                $reponse = new Reponse();
                $reponse->setReponse($form->get('reponse_' . $i)->getData())
                    ->setReponseExpected($i == 1 ? 1 : 0)
                    ->setQuestion($question);
                $em->persist($reponse);
            }

            $question->setCategorie($categorie);

            $em->persist($question);
            $em->persist($categorie);
            $em->flush();

            return $this->redirectToRoute('creation_add_question', ['slug' => $slug]);
        }

        return $this->render('creation/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("creation/{slug}/add", name="creation_add_question")
     */
    public function addQuestion(Request $request, Categorie $categorie, SessionInterface $session)
    {
        if (!$session->get('add_auth')){
            if (($submittedToken = $request->request->get('next-token'))) {
                if (!$this->isCsrfTokenValid($session->get('page_token'), $submittedToken)) {
                    if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                        throw new AccessDeniedException('Access denied');
                    }
                }
            } else if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException('Access denied');
            }
        } else {
            $session->remove('add_auth');
        }

        $session->set('page_token', md5(random_bytes(5)));

        $form = $this->createForm(AddQuestionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $question = new Question();
            $question->setQuestion($form->get('question')->getData());

            for ($i = 1; $i <= 3; $i++) {
                $reponse = new Reponse();
                $reponse->setReponse($form->get('reponse_' . $i)->getData())
                    ->setReponseExpected($i == 1 ? 1 : 0)
                    ->setQuestion($question);
                $em->persist($reponse);
            }

            $question->setCategorie($categorie);

            $em->persist($question);
            $em->flush();

            $form = $this->createForm(AddQuestionType::class);

            return $this->render('creation/add.html.twig', [
                'categorie' => $categorie,
                'form' => $form->createView(),
                'success' => true
            ]);
        }

        return $this->render('creation/add.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }
}
