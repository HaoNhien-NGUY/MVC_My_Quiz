<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function home()
    {
        $test = 'hello homepage';
        return $this->render('home/home.html.twig', ['test' => $test]);
    }
}