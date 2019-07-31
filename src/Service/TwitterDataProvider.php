<?php


namespace App\Service;


use Generator;

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

    public function getUserTweets(string $username): Generator
    {
        yield from $this->twitterApi->getUserTweets($username);
    }
}