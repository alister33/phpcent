<?php

namespace phpcent;

/**
 * Created by PhpStorm.
 * User: komrakov
 * Date: 01.12.15
 * Time: 13:40
 */
interface AuthInterface
{

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
    public function generateToken($secret, $user, $timestamp, $info = "");

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
    public function generateChannelSign($secret, $client, $channel, $info = "");

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
    public function generateApiSign($secret, $encoded_data);

}