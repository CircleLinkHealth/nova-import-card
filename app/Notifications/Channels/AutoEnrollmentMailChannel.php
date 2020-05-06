<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use Illuminate\Notifications\Messages\MailMessage;

class AutoEnrollmentMailChannel extends MailMessage
{
    public function data()
    {
        return array_merge(parent::data(), ['excludeLogo' => true]);
    }
}
