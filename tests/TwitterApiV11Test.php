<?php

namespace App\Tests;

use App\Exception\TwitterAPIException;
use App\Service\TwitterApiV11;
use App\Service\TwitterConnection;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class TwitterApiV11Test extends TestCase
{
    const USERNAME = 'tesuname';
    const LATEST = 123;
    /**
     * @var TwitterConnection|Mockery\MockInterface
     */
    private $connection;
    /**
     * @var Mockery\MockInterface|Logger
     */
    private $logger;
    /**
     * @var TwitterApiV11
     */
    private $api;
    /**
     * @var array
     */
    private $properResponseObject;
    /**
     * @var array
     */
    private $properResponseArray;

    public function setUp()
    {
        $this->properResponseObject = [(object)[
            'id' => 123,
            'id_str' => "123",
            'created_at' => 'Sat Aug 03 14:39:32 +0000 2019',
            'text' => 'Test',
            'user' => (object)['screen_name' => self::USERNAME],
            'entities' => (object)['urls' => [(object)['expanded_url' => 'http://test.com']]]
        ]];

        $this->properResponseArray = [
            0 => [
                'id' => 123,
                'created' => '2019-08-03 14:39:32',
                'content' => 'Test',
                'url' => 'https://twitter.com/tesuname/status/123',
                'links' => [
                    0 => ['url' => 'http://test.com',],
                ],
            ],
        ];

        $this->connection = Mockery::mock(TwitterConnection::class);

        $this->logger = Mockery::mock(Logger::class);
        $this->logger->shouldReceive('error')->withNoArgs()->andReturnNull();
        $this->api = new TwitterApiV11($this->logger, $this->connection);
    }

    public function testGetUserTweets()
    {
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_USER_TWEETS, [
            'screen_name' => self::USERNAME,
            "count" => 25,
            "exclude_replies" => true
        ])->andReturn($this->properResponseObject);

        $result = $this->api->getUserTweets(self::USERNAME);
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
        $this->assertEquals($this->properResponseArray, iterator_to_array($result));
    }

    public function testGetUserTweets_noLinks()
    {
        $this->properResponseObject[0]->entities->urls = [];
        $this->properResponseArray[0]['links'] = [];

        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_USER_TWEETS, [
            'screen_name' => self::USERNAME,
            "count" => 25,
            "exclude_replies" => true
        ])->andReturn($this->properResponseObject);

        $result = $this->api->getUserTweets(self::USERNAME);
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
        $this->assertEquals($this->properResponseArray, iterator_to_array($result));
    }

    public function testGetUserTweets__removeLinksFromText()
    {
        $this->properResponseObject[0]->text = 'Test https://t.co/h2ytbv4CKZ';
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_USER_TWEETS, [
            'screen_name' => self::USERNAME,
            "count" => 25,
            "exclude_replies" => true
        ])->andReturn($this->properResponseObject);

        $result = $this->api->getUserTweets(self::USERNAME);
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
        $this->assertEquals($this->properResponseArray, iterator_to_array($result));
    }

    public function testGetUserTweets__since()
    {
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_USER_TWEETS, [
            'screen_name' => self::USERNAME,
            "count" => 25,
            "exclude_replies" => true,
            "since_id" => "123",
        ])->andReturn($this->properResponseObject);

        $result = $this->api->getUserTweets(self::USERNAME, "123");
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
        $this->assertEquals($this->properResponseArray, iterator_to_array($result));
    }

    public function testGetUserTweets__couldNotAuthenticateError()
    {
        $this->expectException(TwitterAPIException::class);
        $this->expectExceptionMessage(TwitterAPIException::AUTHENTICATION_ERROR);
        $this->connection->shouldReceive('getJSONResponse')->withAnyArgs()
            ->andReturn((object)['errors' => [(object)['message' => 'Er', 'code' => TwitterApiV11::COULD_NOT_AUTHENTICATE_ERROR_CODE]]]);
        $this->logger->shouldReceive('error')->with("Er")->andReturnNull();

        $result = $this->api->getUserTweets(self::USERNAME);
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
    }

    public function testGetUserTweets__GeneralError()
    {
        $this->expectException(TwitterAPIException::class);
        $this->expectExceptionMessage(TwitterAPIException::GENERAL_ERROR);
        $this->connection->shouldReceive('getJSONResponse')->withAnyArgs()
            ->andReturn((object)['errors' => [(object)['message' => 'Er2', 'code' => 1]]]);
        $this->logger->shouldReceive('error')->with("Er2")->andReturnNull();

        $result = $this->api->getUserTweets(self::USERNAME);
        $this->assertTrue($result instanceof \Generator);
        $this->assertTrue($result->valid());
    }

    public function testTestConnection()
    {
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_TEST_CREDENTIALS, [])
            ->andReturn((object)['test' => 'ok']);
        $this->assertTrue($this->api->testConnection());
    }

    public function testTestConnection_Exception()
    {
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_TEST_CREDENTIALS, [])
            ->andThrow(\Exception::class, "NO");
        $this->logger->shouldReceive('error')->with("NO")->andReturnNull();
        $this->assertFalse($this->api->testConnection());
    }

    public function testTestConnection_Error()
    {
        $this->connection->shouldReceive('getJSONResponse')->with(TwitterApiV11::ENDPOINT_TEST_CREDENTIALS, [])
            ->andReturn((object)['errors' => [(object)['message' => 'Er', 'code' => 1]]]);
        $this->logger->shouldReceive('error')->with("Er")->andReturnNull();
        $this->assertFalse($this->api->testConnection());
    }
}