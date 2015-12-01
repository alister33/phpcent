<?php

namespace phpcent;

use phpcent\Exceptions\BadResponseException;

/**
 * Centrifuge client for communication with centrifugo v1.0 and above
 *
 * User: komrakov
 * Date: 02.04.2015 12:30
 *
 * @version 1.0
 */
class Client implements ServerApiInterface, AuthInterface
{

    protected $guzzle;
    protected $hashing_algorithm;
    protected $api_url;
    protected $secret;
    protected $channel_prefix;

    public function __construct(array $options = [])
    {
        $this->guzzle            = isset($options['guzzle'])            ? $options['guzzle']            : new \GuzzleHttp\Client();
        $this->hashing_algorithm = isset($options['hashing_algorithm']) ? $options['hashing_algorithm'] : "sha256";
        $this->api_url           = isset($options['api_url'])           ? $options['api_url']           : "http://localhost:8000/api/";
        $this->secret            = isset($options['secret'])            ? $options['secret']            : "";
        $this->channel_prefix    = isset($options['channel_prefix'])    ? $options['channel_prefix']    : "";
    }

    /**
     * Publish allows to send message into channel.
     *
     * @param string $channel
     * @param mixed $data
     * @param string $client
     *
     * @return array
     * @throws \Exception
     */
    public function publish($channel, $data, $client = "")
    {
        $channel = $this->addChannelPrefix($channel);
        $request = [
            'method' => 'publish',
            'params' => [
                'channel' => $channel,
                'data'    => $data,
            ],
        ];
        if (!empty($client)) {
            $request['params']['client'] = $client;
        }

        return $this->request($request);
    }

    /**
     * Unsubscribe allows to unsubscribe user from channel.
     *
     * @param string $channel
     * @param string $user_id
     *
     * @return array
     * @throws \Exception
     */
    public function unsubscribe($channel, $user_id)
    {
        $channel = $this->addChannelPrefix($channel);
        $request = [
            'method' => 'unsubscribe',
            'params' => [
                'channel' => $channel,
                'user'    => $user_id,
            ],
        ];

        return $this->request($request);
    }

    /**
     * Disconnect allows to disconnect user by its ID.
     *
     * @param string $user_id
     *
     * @return array
     * @throws \Exception
     */
    public function disconnect($user_id)
    {
        $request = [
            'method' => 'disconnect',
            'params' => [
                'user' => $user_id,
            ],
        ];

        return $this->request($request);
    }

    /**
     * Presence allows to get channel presence information (all clients currently subscribed on this channel).
     *
     * @param string $channel
     *
     * @return array
     * @throws \Exception
     */
    public function presence($channel)
    {
        $channel = $this->addChannelPrefix($channel);
        $request = [
            'method' => 'presence',
            'params' => [
                'channel' => $channel,
            ],
        ];

        return $this->request($request);
    }

    /**
     * History allows to get channel history information (list of last messages sent into channel).
     *
     * @param string $channel
     *
     * @return array
     * @throws \Exception
     */
    public function history($channel)
    {
        $channel = $this->addChannelPrefix($channel);
        $request = [
            'method' => 'history',
            'params' => [
                'channel' => $channel,
            ],
        ];

        return $this->request($request);
    }

    /**
     * Channels method allows to get list of active (with one or more subscribers) channels.
     *
     * @return array
     * @throws \Exception
     */
    public function channels()
    {
        $request = [
            'method' => 'channels',
            'params' => [],
        ];

        return $this->request($request);
    }

    /**
     * Stats method allows to get statistics about running Centrifugo nodes.
     *
     * @return array
     * @throws \Exception
     */
    public function stats()
    {
        $request = [
            'method' => 'stats',
            'params' => [],
        ];

        return $this->request($request);
    }

    /**
     * Method for sending signed api requests
     *
     * @param array $request
     *
     * @return array
     * @throws BadResponseException
     */
    public function request(array $request)
    {
        $encoded_data = json_encode($request);
        $sign = $this->generateApiSign($this->secret, $encoded_data);
        $body = $this->guzzle->post($this->api_url, ['form_params' => ['sign' => $sign, 'data' => $encoded_data]])->getBody();
        $result = json_decode($body, true);
        if (!isset($result[0])) {
            throw new BadResponseException("Invalid response format");
        }

        return $result[0];
    }

    /**
     * @param \GuzzleHttp\Client $guzzle
     *
     * @return $this
     */
    public function withGuzzle($guzzle)
    {
        $this->guzzle = $guzzle;

        return $this;
    }

    /**
     * @param string $hashing_algorithm
     *
     * @return $this
     */
    public function withHashingAlgorithm($hashing_algorithm)
    {
        $this->hashing_algorithm = $hashing_algorithm;

        return $this;
    }

    /**
     * @param string $api_url
     *
     * @return $this
     */
    public function withApiUrl($api_url)
    {
        $this->api_url = $api_url;

        return $this;
    }

    /**
     * @param string $secret
     *
     * @return $this
     */
    public function withSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * @param string $channel_prefix
     *
     * @return $this
     */
    public function withChannelPrefix($channel_prefix)
    {
        $this->channel_prefix = $channel_prefix;

        return $this;
    }

    /**
     * When client connects to Centrifuge from browser it should provide several
     * connection parameters: "user", "timestamp", "info" and "token".
     *
     * @link https://fzambia.gitbooks.io/centrifugal/content/server/tokens_and_signatures.html
     *
     * @param $secret
     * @param $user
     * @param $timestamp
     * @param string $info
     *
     * @return string
     */
    public function generateToken($secret, $user, $timestamp, $info = "")
    {
        $context = hash_init($this->hashing_algorithm, HASH_HMAC, $secret);
        hash_update($context, $user);
        hash_update($context, $timestamp);
        hash_update($context, $info);

        return hash_final($context);
    }

    /**
     * When client wants to subscribe on private channel Centrifuge
     * js client sends AJAX POST request to your web application.
     * This request contains client ID string and one or multiple private channels.
     * In response you should return an object where channels are keys.
     *
     * @link https://fzambia.gitbooks.io/centrifugal/content/server/tokens_and_signatures.html
     *
     * @param $secret
     * @param $client
     * @param string $channel
     * @param string $info
     *
     * @return string
     */
    public function generateChannelSign($secret, $client, $channel, $info = "")
    {
        $context = hash_init($this->hashing_algorithm, HASH_HMAC, $secret);
        hash_update($context, $client);
        hash_update($context, $channel);
        hash_update($context, $info);

        return hash_final($context);
    }

    /**
     * When you use Centrifugo server API you should also provide sign in each request.
     *
     * @link https://fzambia.gitbooks.io/centrifugal/content/server/tokens_and_signatures.html
     *
     * @param $secret
     * @param $encoded_data
     *
     * @return string
     */
    public function generateApiSign($secret, $encoded_data)
    {
        $context = hash_init($this->hashing_algorithm, HASH_HMAC, $secret);
        hash_update($context, $encoded_data);

        return hash_final($context);
    }

    /**
     * @param $channel
     * @return string
     */
    private function addChannelPrefix($channel)
    {
        return $this->channel_prefix . $channel;
    }


    /**
     * @return \GuzzleHttp\Client
     */
    public function getGuzzle()
    {
        return $this->guzzle;
    }

    /**
     * @return string
     */
    public function getHashingAlgorithm()
    {
        return $this->hashing_algorithm;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return string
     */
    public function getChannelPrefix()
    {
        return $this->channel_prefix;
    }

}