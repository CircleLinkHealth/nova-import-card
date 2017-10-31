<?php namespace App\CLH\Contracts\Messaging;

interface SecureMessage
{

    /**
     * This method will send a message to the user through an api.
     *
     * @param $to
     * @param $from
     * @param $message
     * @param array $args
     * @return mixed
     */
    public function send($to, $from, $message, $args = []);
}
