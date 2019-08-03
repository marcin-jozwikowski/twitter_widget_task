<?php

namespace App\Controller;

use App\Exception\TwitterAPIException;
use App\Form\UsernameForm;
use App\Service\TwitterDataProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwitterController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $username = '';
        $form = $this->createForm(UsernameForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->getData()['username'];
        }

        return $this->render('twitter/index.html.twig', [
            'form' => $form->createView(),
            'username' => $username
        ]);
    }

    /**
     * @Route("/tweets/{username}/{latest}", defaults={"latest"=null}, name="data_parse", requirements={"username"="[\w\d_]{1,15}", "latest"="[\d]{18,}"})
     * @param string $username
     * @param string $latest
     * @param TwitterDataProvider $twitter
     * @return JsonResponse
     */
    public function getData(string $username, TwitterDataProvider $twitter, string $latest = null)
    {
        $result = [
            "status" => "ERROR",
            "message" => "unknown"
        ];
        try {
            // tweets need to be in reverse order
            $result = [
                "status" => "OK",
                "tweets" => array_reverse(iterator_to_array($twitter->getUserTweets($username, $latest), true))
            ];
        } catch (TwitterAPIException $e) {
            $result["message"] = $e->getMessage();
        }

        return $this->json($result);
    }
}