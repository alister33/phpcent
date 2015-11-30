<?php

/**
 * Created by PhpStorm.
 * User: komrakov
 * Date: 30.11.15
 * Time: 11:09
 */
class ClientTest extends PHPUnit_Framework_TestCase
{

    public $options = [
        'api_url' => "http://localhost:8000/api/",
        'secret'  => "my_secret_key",
    ];

    public function testPublish()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->publish("test_channel", "");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);

        $result = $client->publish("test_channel", [], "test_client");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);
    }

    public function testUnsubscribe()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->unsubscribe("test_channel", "qwe");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);

        $result = $client->unsubscribe("test_channel", "qweqweqwe");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);
    }

    public function testDisconnect()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->disconnect("user_id");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);
    }

    public function testPresence()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->presence("my_channel");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals("not available", $result['error']);
    }

    public function testHistory()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->history("my_channel");
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals("not available", $result['error']);
    }

    public function testChannels()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->channels();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);
    }

    public function testStats()
    {
        $client = new \phpcent\Client($this->options);
        $result = $client->stats();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['error']);

        $this->assertArrayHasKey('body', $result);
        $this->assertArrayHasKey('data', $result['body']);
        $this->assertArrayHasKey('nodes', $result['body']['data']);
        $this->assertInternalType('array', $result['body']['data']['nodes']);
        $this->assertNotEmpty($result['body']['data']['nodes']);
    }

}
