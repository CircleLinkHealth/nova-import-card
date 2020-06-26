<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use App\Contracts\FaxableNotification;
use App\FaxLog;
use Phaxio\Fax;

class PhaxioFaxServiceLogger implements Efax
{
    /**
     * @var Efax
     */
    protected $efax;

    public function __construct(Efax $efax)
    {
        $this->efax = $efax;
    }

    public function createFaxFor(string $number): Efax
    {
        return $this->efax->createFaxFor($number);
    }

    /**
     * Send a fax.
     *
     * https://www.phaxio.com/docs/api/v2.1/faxes/create_and_send_fax
     *
     * @param string $to
     *
     * @return mixed
     */
    public function send(array $options = [])
    {
        $response = $this->efax->send($options);

        if ($this->shouldLogResponse($response->getArrayCopy())) {
            $log = $this->logResponse($response);
        }

        return $response;
    }

    /**
     * Send a FaxableNotification Object via fax.
     *
     * @param $notifiable
     *
     * @return mixed
     */
    public function sendNotification($notifiable, FaxableNotification &$notification, array $options = [])
    {
        return $this->efax->sendNotification($notifiable, $notification);
    }

    public function setOption(string $string, $array): Efax
    {
        return $this->efax->setOption($string, $array);
    }

    private function logResponse(Fax $response)
    {
        $response = $response->retrieve();

        return FaxLog::create(
            [
                'fax_id'    => $response['id'],
                'status'    => $response['status'],
                'direction' => $response['direction'],
                'response'  => $response,
            ]
        );
    }

    private function shouldLogResponse(array $response)
    {
        return array_key_exists('id', $response) && ! empty($response['id']);
    }
}
