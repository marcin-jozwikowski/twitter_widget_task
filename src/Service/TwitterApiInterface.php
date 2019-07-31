<?php


namespace App\Service;


interface TwitterApiInterface
{
    function getUserTweets(string $username): array;
    function testConnection(): bool;
}