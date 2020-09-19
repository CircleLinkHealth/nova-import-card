<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

use CircleLinkHealth\Core\Contracts\FaxableNotification;

interface Efax
{
    public function createFaxFor(string $number): Efax;

    /**
     * Send a fax.
     *
     * https://www.phaxio.com/docs/api/v2.1/faxes/create_and_send_fax
     *
     * @return mixed
     */
    public function send(
        array $options = []
    );

    /**
     * Send a FaxableNotification Object via fax.
     *
     * @param $notifiable
     *
     * @return mixed
     */
    public function sendNotification($notifiable, FaxableNotification $notification, array $options = []);

    public function setOption(string $string, $array): Efax;
}
