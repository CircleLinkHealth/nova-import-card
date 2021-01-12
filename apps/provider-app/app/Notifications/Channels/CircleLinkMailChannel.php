<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Notifications\Channels;

use Illuminate\Notifications\Messages\MailMessage;

class CircleLinkMailChannel extends MailMessage
{
    /**
     * @var
     */
    public $emailData;
    public $url;

    public function __construct($emailData, $url)
    {
        $this->emailData = $emailData;
        $this->url       = $url;
    }

    /**
     * @return array
     */
    public function data()
    {
        return array_merge(parent::data(), ['emailData' => $this->emailData, 'url' => $this->url]);
    }
}
