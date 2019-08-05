<?php


namespace App\Service;


use App\Service\Connection\GetRequestInterface;
use Exception;
use Psr\Log\LoggerInterface;

class TwitterConnection implements TwitterConnectionInterface
{
    const API_URL_FORMAT = "https://api.twitter.com/1.1/%s.json";
    /**
     * @var string
     */
    private $consumerKey;
    /**
     * @var string
     */
    private $consumerSecret;
    /**
     * @var string
     */
    private $oauthToken;
    /**
     * @var string
     */
    private $oauthTokenSecret;
    /**
     * @var GetRequestInterface
     */
    private $connection;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TwitterConnection constructor.
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $oauthToken
     * @param string $oauthTokenSecret
     * @param GetRequestInterface $connection
     * @param LoggerInterface $logger
     */
    public function __construct(string $consumerKey, string $consumerSecret, string $oauthToken, string $oauthTokenSecret, GetRequestInterface $connection, LoggerInterface $logger)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->oauthToken = $oauthToken;
        $this->oauthTokenSecret = $oauthTokenSecret;
        $this->connection = $connection;
        $this->logger = $logger;
    }

    /**
     * @param $path
     * @param array $parameters
     * @return mixed
     * @throws Exception
     */
    function getJSONResponse($path, array $parameters = [])
    {
        $query = empty($parameters) ? '' : '?' . http_build_query($parameters, '', '&');
        $url = sprintf(self::API_URL_FORMAT, $path);

        $rawResult = $this->connection->get($url . $query, [$this->getOAuthHeader($url, $parameters)]);

        $result = json_decode($rawResult);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        return $result;
    }

    /**
     * @param string $url
     * @param array $queryParams
     * @return string
     */
    private function getOAuthHeader(string $url, array $queryParams)
    {
        // as described in https://developer.twitter.com/en/docs/basics/authentication/guides/authorizing-a-request
        $oauth = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->oauthToken,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        ];
        $this->addOAuthSignature($oauth, $url, $queryParams);
        $this->logger->debug("OAuth keys generated", $oauth);

        $header = [];
        foreach ($oauth as $key => $value) {
            $header[] = sprintf('%s="%s"', $key, rawurlencode($value));
        }

        return 'Authorization: OAuth ' . implode(', ', $header);
    }

    /**
     * @param array $oauth
     * @param string $url
     * @param array $queryParams
     */
    private function addOAuthSignature(array &$oauth, string $url, array $queryParams)
    {
        // Described in https://developer.twitter.com/en/docs/basics/authentication/guides/creating-a-signature.html
        $oauthCoded = [];
        foreach (array_merge($oauth, $queryParams) as $oaKey => $oaValue) {
            $oauthCoded[rawurlencode($oaKey)] = rawurlencode($oaValue);
        }
        ksort($oauthCoded);
        $this->logger->debug("OAuth keys percentCoded", $oauthCoded);

        $oaBase = [];
        foreach ($oauthCoded as $oaKey => $oaValue) {
            $oaBase[] = $oaKey . '=' . $oaValue;
        }

        $baseURL = "GET&" . rawurlencode($url) . '&' . rawurlencode(implode('&', $oaBase));
        $this->logger->debug("OAuth base URL: " . $baseURL);

        $signingKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->oauthTokenSecret);

        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $baseURL, $signingKey, true));
    }
}