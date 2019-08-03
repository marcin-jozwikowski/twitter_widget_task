<?php


namespace App\Service;


use Abraham\TwitterOAuth\TwitterOAuthException;
use App\Exception\TwitterAPIException;
use DateTime;
use Generator;
use Psr\Log\LoggerInterface;

class TwitterApiV11 implements TwitterApiInterface
{
    const ENDPOINT_TEST_CREDENTIALS = "account/verify_credentials";
    const ENDPOINT_USER_TWEETS = 'statuses/user_timeline';
    const COULD_NOT_AUTHENTICATE_ERROR_CODE = 32;
    const URL_FORMAT = "https://twitter.com/%s/status/%s";

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var TwitterConnectionInterface
     */
    private $connection;

    public function __construct(LoggerInterface $logger, TwitterConnectionInterface $connection)
    {
        $this->logger = $logger;
        $this->connection = $connection;
    }

    /**
     * @param string $username
     * @param string|null $latest
     * @return Generator
     * @throws TwitterAPIException
     */
    function getUserTweets(string $username, string $latest = null): Generator
    {
        $params = [
            'screen_name' => $username,
            "count" => 25,
            "exclude_replies" => true
        ];
        if (!empty($latest)) {
            $params["since_id"] = $latest;
        }
        $result = $this->getJsonResponse(self::ENDPOINT_USER_TWEETS, $params);

        yield from $this->parseTweets($result);
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
            $result = $this->connection->get($uri, $params);
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

    private function parseTweets(array $result): Generator
    {
        foreach ($result as $id => $tweet) {
            // remove content links
            $content = trim(preg_replace('/(https?:\/\/t.co\/[\w\d]*)/i', '', $tweet->text));
            $links = [];
            foreach ($tweet->entities->urls as $url) {
                $links[] = [
                    'url' => $url->expanded_url,
                ];
            }
            yield [
                "id" => $tweet->id,
                "created" => (new DateTime($tweet->created_at))->format('Y-m-d H:i:s'),
                "content" => empty($content) ? $tweet->text : $content,
                "url" => sprintf(self::URL_FORMAT, $tweet->user->screen_name, $tweet->id_str),
                "links" => $links
            ];
        }
    }
}