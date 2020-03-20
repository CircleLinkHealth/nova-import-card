<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Contracts;

use App\ValueObjects\SimpleNotification;

interface DirectMailableNotification
{
    /**
     * @param $notifiable
     */
    public function directMailBody($notifiable): string;

    /**
     * @param $notifiable
     */
    public function directMailSubject($notifiable): string;

    /**
     * @param $notifiable
     */
    public function toDirectMail($notifiable): SimpleNotification;
}
