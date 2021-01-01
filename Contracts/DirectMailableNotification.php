<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Contracts;

use CircleLinkHealth\Core\DTO\SimpleNotification;

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
