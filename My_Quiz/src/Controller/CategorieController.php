<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Reponse;
use App\Entity\Resultat;
use App\Form\AnswerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
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
     * @Route("quiz/create", name="categorie_create_test")
     */
    public function create()
    {
        $slugger = new AsciiSlugger();
        $manager = $this->getDoctrine()->getManager();
        $categorie = new Categorie();
        $categorie->setName('Musique classique');
        $slug = $slugger->slug('musique-classique')->lower();
        $categorie->setSlug($slug);
        // $manager->persist($categorie);
        // $manager->flush();

        return $this->render('base.html.twig');
    }

    /**
     * @Route("/quiz/{slug}/results", methods="POST", name="categorie_results")
     */
    public function showResults(Categorie $categorie, SessionInterface $session, Security $security)
    {
        //csrf token validation here


        //add results in database
        $user = $security->getUser();
        $sessionAnswer = $session->get('userAnswer');
        $userAnswer = array_keys($sessionAnswer);
        $questions = $categorie->getQuestions();
        $nbCorrect = array_count_values($sessionAnswer)[1];
        $totalQuestion = $questions->count();
        
        if($user)
        {
            $resultat = new Resultat();
            $resultat->setUser($user)
            ->setCategorie($categorie)
            ->setNote($nbCorrect);
            $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($resultat);
            // $entityManager->flush();
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
     * @Route("/quiz/{slug}", name="categorie_details")
     */
    public function showDetails(Categorie $categorie)
    {
        // Category stats logic

        $nbQuestion = $categorie->getQuestions()->count();

        return $this->render('categorie/details.html.twig', [
            'categorie' => $categorie,
            'nbQuestion' => $nbQuestion
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
            if (!$this->isCsrfTokenValid('next-question' . $nbquestion, $submittedToken)) {
                throw new AccessDeniedException('Access denied');
            }
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
