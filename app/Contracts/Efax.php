<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

interface Efax
{
    public function getStatus($faxId);

    public function send(
        $faxNumber,
        $pathOrMessage
    );
}
