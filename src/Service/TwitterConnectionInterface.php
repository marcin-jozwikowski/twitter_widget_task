<?php


namespace App\Service;


interface TwitterConnectionInterface
{
    function get($path, array $parameters = []);
}