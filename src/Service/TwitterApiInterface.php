<?php


namespace App\Service;


use Generator;

interface TwitterApiInterface
{
    function getUserTweets(string $username, string $latest = null): Generator;
    function testConnection(): bool;
}