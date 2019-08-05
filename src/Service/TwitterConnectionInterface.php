<?php


namespace App\Service;


interface TwitterConnectionInterface
{
    function getJSONResponse($path, array $parameters = []);
}