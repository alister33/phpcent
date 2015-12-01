<?php

namespace phpcent;

/**
 * Created by PhpStorm.
 * User: komrakov
 * Date: 01.12.15
 * Time: 13:23
 */
interface ServerApiInterface
{

    /**
     * Publish allows to send message into channel.
     *
     * @param string $channel
     * @param mixed $data
     * @param string $client
     *
     * @return array
     */
    public function publish($channel, $data, $client = "");

    /**
     * Unsubscribe allows to unsubscribe user from channel.
     *
     * @param string $channel
     * @param string $user_id
     *
     * @return array
     */
    public function unsubscribe($channel, $user_id);

    /**
     * Disconnect allows to disconnect user by its ID.
     *
     * @param string $user_id
     *
     * @return array
     */
    public function disconnect($user_id);

    /**
     * Presence allows to get channel presence information (all clients currently subscribed on this channel).
     *
     * @param string $channel
     *
     * @return array
     */
    public function presence($channel);

    /**
     * History allows to get channel history information (list of last messages sent into channel).
     *
     * @param string $channel
     *
     * @return array
     */
    public function history($channel);

    /**
     * Channels method allows to get list of active (with one or more subscribers) channels.
     *
     * @return array
     */
    public function channels();

    /**
     * Stats method allows to get statistics about running Centrifugo nodes.
     *
     * @return array
     */
    public function stats();

}