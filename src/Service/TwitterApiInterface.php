<?php


namespace App\Service;


use Generator;

interface TwitterApiInterface
{
    function getUserTweets(string $username): Generator;
    function testConnection(): bool;
}