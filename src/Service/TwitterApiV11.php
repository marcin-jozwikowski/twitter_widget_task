<?php


namespace App\Service;


use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Exception\TwitterAPIException;
use Psr\Log\LoggerInterface;

class TwitterApiV11 implements TwitterApiInterface
{
    const ENDPOINT_TEST_CREDENTIALS = "account/verify_credentials";
    const ENDPOINT_USER_TWEETS = 'statuses/user_timeline';
    const COULD_NOT_AUTHENTICATE_ERROR_CODE = 32;

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
    private $token;
    /**
     * @var string
     */
    private $tokenSecret;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, string $consumerKey, string $consumerSecret, string $token, string $tokenSecret)
    {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;
        $this->logger = $logger;
    }

    /**
     * @param string $username
     * @return array
     * @throws TwitterAPIException
     */
    function getUserTweets(string $username): array
    {
        return $this->getJsonResponse(self::ENDPOINT_USER_TWEETS, [
            'screen_name' => $username,
            "count" => 25,
            "exclude_replies" => true
        ]);
    }

    function testConnection(): bool
    {
        try {
            $this->getJsonResponse(self::ENDPOINT_TEST_CREDENTIALS);
        } catch (TwitterAPIException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param string $uri
     * @param array|null $params
     * @return array
     * @throws TwitterAPIException
     */
    private function getJsonResponse(string $uri, ?array $params = []): array
    {
        try {
            $connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->token, $this->tokenSecret);
            $result = $connection->get($uri, $params);
        } catch (TwitterOAuthException $e) {
            $this->logger->error($e->getMessage());
            throw new TwitterAPIException(TwitterAPIException::AUTHENTICATION_ERROR);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new TwitterAPIException(TwitterAPIException::GENERAL_ERROR);
        }

        if (isset($result->errors) && 0 < count($result->errors)) {
            foreach ($result->errors as $error) {
                $this->logger->error($error->message);
            }
            if (self::COULD_NOT_AUTHENTICATE_ERROR_CODE === $result->errors[0]->code) {
                throw new TwitterAPIException(TwitterAPIException::AUTHENTICATION_ERROR);
            }
            throw new TwitterAPIException(TwitterAPIException::GENERAL_ERROR);
        }

        return (array)$result;
    }
}