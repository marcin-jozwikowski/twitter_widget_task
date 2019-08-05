<?php


namespace App\Service\Connection;


use Exception;

class CurlGetConnection implements GetRequestInterface
{
    /**
     * @param string $url
     * @param array|null $headers
     * @return string
     * @throws Exception
     */
    function get(string $url, ?array $headers): string
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $json = curl_exec($ch);

        if ($error = curl_error($ch)) {
            throw new Exception($error);
        }

        return $json;
    }
}