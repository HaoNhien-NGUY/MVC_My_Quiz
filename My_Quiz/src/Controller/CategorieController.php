<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Reponse;
use App\Entity\Resultat;
use App\Form\AnswerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class CategorieController extends AbstractController
{
    /**
     * @Route("/quiz", name="categorie")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $query = $this->getDoctrine()->getRepository(Categorie::class)->findAll();

        $categories = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/quiz/{slug}", name="categorie_details")
     */
    public function showDetails(Categorie $categorie)
    {
        $resultats = $categorie->getResultats();
        $nbQuestion = $categorie->getQuestions()->count();
        $avg = null;
        if (!$resultats->isEmpty()) {
            foreach ($resultats as $resultat) {
                $avg += $resultat->getNote();
            }
            $avg = $avg / $resultats->count();
        }
        $resultats = array_reverse($resultats->toArray());

        return $this->render('categorie/details.html.twig', [
            'categorie' => $categorie,
            'nbQuestion' => $nbQuestion,
            'resultats' => $resultats,
            'avg' => $avg,
        ]);
    }

    /**
     * @Route("/quiz/{slug}/results", methods="POST", name="categorie_results")
     */
    public function showResults(Request $request, Categorie $categorie, SessionInterface $session, Security $security)
    {
        if ($submittedToken = $request->request->get('next-token')) {
            if (!$this->isCsrfTokenValid($session->get('page_token'), $submittedToken)) {
                throw new AccessDeniedException('Access denied');
            }
        } else {
            throw new AccessDeniedException('Access denied');
        }

        //add results in database
        $user = $security->getUser();
        $sessionAnswer = $session->get('userAnswer');
        $userAnswer = array_keys($sessionAnswer);
        $questions = $categorie->getQuestions();
        $nbCorrect = array_count_values($sessionAnswer);
        $nbCorrect = isset($nbCorrect[1]) ? $nbCorrect[1] : 0;
        $totalQuestion = $questions->count();

        if ($user) {
            $resultat = new Resultat();
            $resultat->setUser($user)
                ->setCategorie($categorie)
                ->setNote($nbCorrect);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($resultat);
            $entityManager->flush();
        }

        return $this->render('categorie/results.html.twig', [
            'categorie' => $categorie,
            'nbCorrect' => $nbCorrect,
            'totalQuestion' => $totalQuestion,
            'questions' => $questions,
            'userAnswer' => $userAnswer
        ]);
    }

    /**
     * @Route("/quiz/{slug}/1", name="categorie_question", requirements={"question"="[1-9]\d*"})
     */
    public function startQuestion(Request $request, Categorie $categorie, SessionInterface $session)
    {
        $question = ($categorie->getQuestions())[0];
        $reponses = $question->getReponses();

        //suffle answer order
        $shuffledAnswer = $reponses->toArray();
        shuffle($shuffledAnswer);
        //create choice array
        foreach ($shuffledAnswer as $reponse) {
            $reponseArray[$reponse->getReponse()] = $reponse->getReponse();
        }

        $form = $this->createForm(AnswerType::class, $reponseArray);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('page_token', md5(random_bytes(5)));

            $userAnswer = $form->get('answer')->getData();
            $correctAnswer = $reponses->filter(function (Reponse $reponse) {
                return $reponse->getReponseExpected() == 1;
            })->first()->getReponse();

            //set quiz results in session
            $sessionAnswer[$userAnswer] = $correctAnswer == $userAnswer ? 1 :  0;
            $session->set('userAnswer', $sessionAnswer);

            return $this->render('categorie/question.html.twig', [
                'categorie' => $categorie,
                'question' => $question,
                'correctAnswer' => $correctAnswer,
                'userAnswer' => $userAnswer,
                'nextQuestion' => 2
            ]);
        }

        return $this->render('categorie/question.html.twig', [
            'categorie' => $categorie,
            'question' => $question,
            'form' => $form->createView(),
            'progress' => 0
        ]);
    }

    /**
     * @Route("/quiz/{slug}/{nbquestion}", methods="POST", name="categorie_next_question", requirements={"question"="[2-9]\d*"})
     */
    public function nextQuestion(Request $request, Categorie $categorie, $nbquestion, SessionInterface $session)
    {
        if ($submittedToken = $request->request->get('next-token')) {
            if (!$this->isCsrfTokenValid($session->get('page_token'), $submittedToken)) {
                throw new AccessDeniedException('Access denied');
            }
        } else {
            throw new AccessDeniedException('Access denied');
        }

        $questions = $categorie->getQuestions();
        $totalQuestion = $questions->count();
        $progress = ($nbquestion / $totalQuestion) * 100;

        $question = $questions[$nbquestion - 1];
        $reponses = $question->getReponses();

        //suffle answer order
        $shuffledAnswer = $reponses->toArray();
        shuffle($shuffledAnswer);
        //create choice array
        foreach ($shuffledAnswer as $reponse) {
            $reponseArray[$reponse->getReponse()] = $reponse->getReponse();
        }

        $form = $this->createForm(AnswerType::class, $reponseArray);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->set('page_token', md5(random_bytes(5)));

            $userAnswer = $form->get('answer')->getData();
            $correctAnswer = $reponses->filter(function (Reponse $reponse) {
                return $reponse->getReponseExpected() == 1;
            })->first()->getReponse();

            //set quiz results in session
            $sessionAnswer = $session->get('userAnswer');
            $sessionAnswer[$userAnswer] = $correctAnswer == $userAnswer ? 1 :  0;
            $session->set('userAnswer', $sessionAnswer);

            return $this->render('categorie/question.html.twig', [
                'categorie' => $categorie,
                'question' => $question,
                'correctAnswer' => $correctAnswer,
                'userAnswer' => $userAnswer,
                'nextQuestion' => $nbquestion == $totalQuestion ? 0 : $nbquestion + 1
            ]);
        }

        return $this->render('categorie/question.html.twig', [
            'categorie' => $categorie,
            'question' => $question,
            'form' => $form->createView(),
            'progress' => $progress
        ]);
    }
}
