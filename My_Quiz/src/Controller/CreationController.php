<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CreationController extends AbstractController
{
    /**
     * @Route("/creation", name="creation_index")
     */
    public function index()
    {

        //if submited, return redirect to creation/slug

        return $this->render('creation/index.html.twig', [
            'controller_name' => 'CreationController',
        ]);
    }

    /**
     * @Route("creation/create", name="creation_create")
     */
    public function create()
    {
        // $slugger = new AsciiSlugger();
        // $manager = $this->getDoctrine()->getManager();
        // $categorie = new Categorie();
        // $categorie->setName('Musique classique');
        // $slug = $slugger->slug('musique-classique')->lower();
        // $categorie->setSlug($slug);
        // $manager->persist($categorie);
        // $manager->flush();

        return $this->render('base.html.twig');
    }
}
