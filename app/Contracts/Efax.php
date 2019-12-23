<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Efax
{
    /**
     * Send a fax.
     *
     * https://www.phaxio.com/docs/api/v2.1/faxes/create_and_send_fax
     *
     * @param string $to
     * @param array $options
     *
     * @return mixed
     */
    public function send(
        array $options = []
    );
    
    public function createFaxFor(string $number) : Efax;
    
    public function setOption(string $string, $array) : Efax;
}
