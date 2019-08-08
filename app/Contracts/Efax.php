<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Efax
{
    /**
     * @param $faxNumber
     * @param array|string $pathOrMessage
     *
     * @return mixed
     */
    public function send(
        $faxNumber,
        $pathOrMessage
    );
}
