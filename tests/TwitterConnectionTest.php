<?php

namespace App\Tests;

function time()
{
    return "123";
}

use App\Service\Connection\GetRequestInterface;
use App\Service\TwitterConnection;
use Mockery;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TwitterConnectionTest extends TestCase
{
    /**
     * @var GetRequestInterface|Mockery\MockInterface
     */
    private $curl;
    /**
     * @var Mockery\MockInterface|Logger
     */
    private $logger;
    /**
     * @var TwitterConnection
     */
    private $connection;

    public function setUp()
    {
        $this->curl = Mockery::mock(GetRequestInterface::class);
        $this->logger = Mockery::mock(Logger::class);
        $this->logger->shouldReceive('debug')->withAnyArgs()->andReturnNull();

        $this->connection = new TwitterConnection("1", "2", "3", "4", $this->curl, $this->logger);
    }

    public function testGetJSONResponse()
    {
        $this->curl->shouldReceive('get')->withAnyArgs()->andReturn('{"status":"ok"}');
        $r = $this->connection->getJSONResponse("test/me", ["q" => "a"]);

        $this->assertEquals((object)["status" => "ok"], $r);
    }

    public function testGetJSONResponse_error()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Syntax error");
        $this->curl->shouldReceive('get')->withAnyArgs()->andReturn('#$%&^*(');
        $r = $this->connection->getJSONResponse("test/me", ["q" => "a"]);

        $this->assertEquals((object)["status" => "ok"], $r);
    }

    public function testGetJSONResponse_curlException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("CURL EXCEPTION");
        $this->curl->shouldReceive('get')->withAnyArgs()->andThrow(\Exception::class, "CURL EXCEPTION");
        $r = $this->connection->getJSONResponse("test/me", ["q" => "a"]);

        $this->assertEquals((object)["status" => "ok"], $r);
    }

    public function testOAuthSignatureGeneration()
    {
        $oauth = [
            'oauth_consumer_key' => 1,
            'oauth_nonce' => 123,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => 3,
            'oauth_timestamp' => 123,
            'oauth_version' => '1.0'
        ];

        $reflection = new ReflectionClass(get_class($this->connection));
        $method = $reflection->getMethod("addOAuthSignature");
        $method->setAccessible(true);

        $res = $method->invokeArgs($this->connection, [&$oauth, "https://api.me", ["q" => "a"]]);

        $this->assertEquals([
            'oauth_consumer_key' => 1,
            'oauth_nonce' => 123,
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => 3,
            'oauth_timestamp' => 123,
            'oauth_version' => '1.0',
            'oauth_signature' => 'KDDywTaWwqFiua92ef5u4b6atro=',
        ], $oauth);
    }
}
