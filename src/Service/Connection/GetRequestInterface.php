<?php


namespace App\Service\Connection;


interface GetRequestInterface
{
    function get(string $url, ?array $headers): string;
}