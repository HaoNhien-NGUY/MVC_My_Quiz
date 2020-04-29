<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\AnswerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CategorieController extends AbstractController
{
    /**
     * @Route("/categorie", name="categorie")
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
     * @Route("categorie/create", name="categorie_create_test")
     */
    public function create()
    {
        $slugger = new AsciiSlugger();
        $manager = $this->getDoctrine()->getManager();
        $categorie = new Categorie();
        $categorie->setName('Musique classique');
        $slug = $slugger->slug('musique-classique')->lower();
        $categorie->setSlug($slug);
        $manager->persist($categorie);
        $manager->flush();

        return $this->render('base.html.twig');
    }

    /**
     * @Route("/categorie/{slug}", name="categorie_details")
     */
    public function show(Categorie $categorie)
    {
        // Category stats logic

        return $this->render('categorie/details.html.twig', ['categorie' => $categorie]);
    }

    /**
     * @Route("/categorie/{slug}/{question}", name="categorie_question", requirements={"question"="\d+"})
     */
    public function question(Request $request, Categorie $categorie, $question)
    {
        // dump($request->getMethod(), $categorie, $question);
        $question = ($categorie->getQuestions())[$question];
        $reponses = $question->getReponses();

        $form = $this->createFormBuilder()
            ->add(
                'answer',
                ChoiceType::class,
                [
                    'choices' => ['answer1' => '1', 'answer2' => '2', 'answer3' => '3', 'answer4' => '4'],
                    'multiple' => false, 'expanded' => true
                ]
            )
            ->getForm();

        return $this->render('categorie/question.html.twig', [
            'categorie' => $categorie,
            'question' => $question,
            'reponses' => $reponses,
            'form' => $form->createView()
        ]);
    }
}
