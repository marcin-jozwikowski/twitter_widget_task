<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TwitterController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('twitter/index.html.twig');
    }

    /**
     * @Route("/tweets/{username}", name="data_parse", requirements={"username"="[\w\d_]{1,15}"})
     * @param string $username
     * @return JsonResponse
     */
    public function getData(string $username)
    {

        return $this->json(['hello' => 'world']);
    }
}