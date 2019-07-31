<?php


namespace App\Service;


class TwitterDataProvider
{
    /**
     * @var TwitterApiInterface
     */
    private $twitterApi;

    public function __construct(TwitterApiInterface $twitterApi)
    {
        $this->twitterApi = $twitterApi;
    }

    public function getUserTweets(string $username): array
    {
        return $this->twitterApi->getUserTweets($username);
    }
}