<?php
namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home_index")
     */
    public function index(CategorieRepository $repo)
    {
        $categories = $repo->findBy([], ['name' => 'ASC'], 10);   
        return $this->render('home/home.html.twig', ['categories' => $categories]);
    }
}