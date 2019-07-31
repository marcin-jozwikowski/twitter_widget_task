<?php


namespace App\Exception;


class TwitterAPIException extends \Exception
{
    const AUTHENTICATION_ERROR = "Could not login to Twitter";
    const GENERAL_ERROR = "Failed retrieving data";
}