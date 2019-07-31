<?php

namespace App\Controller;

use App\Exception\TwitterAPIException;
use App\Service\TwitterDataProvider;
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
     * @param TwitterDataProvider $twitter
     * @return JsonResponse
     */
    public function getData(string $username, TwitterDataProvider $twitter)
    {
        $result = [
            "status" => "ERROR",
            "message" => "unknown"
        ];
        try {
            $result = [
                "status" => "OK",
                "tweets" => iterator_to_array($twitter->getUserTweets($username), true)
            ];
        } catch (TwitterAPIException $e) {
            $result["message"] = $e->getMessage();
        }

        return $this->json($result);
    }
}