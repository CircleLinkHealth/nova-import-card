<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use Illuminate\Notifications\Messages\MailMessage;

class AutoEnrollmentMailChannel extends MailMessage
{
    /**
     * @var string
     */
    private $practiceName;

    /**
     * AutoEnrollmentMailChannel constructor.
     *
     * @param mixed|null $practiceName
     */
    public function __construct($practiceName = null)
    {
        $this->practiceName = $practiceName;
    }

    public function data()
    {
        return array_merge(parent::data(), ['excludeLogo' => true, 'practiceName' => $this->practiceName]);
    }
}
