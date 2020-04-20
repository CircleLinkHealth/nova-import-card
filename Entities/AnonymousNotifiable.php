<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;

use Illuminate\Notifications\RoutesNotifications;

class AnonymousNotifiable extends \Illuminate\Notifications\AnonymousNotifiable
{
    use RoutesNotifications;

    const ATTRIBUTES = [
        'emr_direct_address',
        'fax',
        'email',
    ];
    const ID = 0;

    /**
     * @param $name
     *
     * @return int
     */
    public function __get($name)
    {
        if ('id' == $name) {
            return self::ID;
        }
        /*
         * Structure of $this->routes (inherited from \Illuminate\Notifications\AnonymousNotifiable) is:
         * ['channel' => $value] e.g. ['mail' => 'test@mail.com']
         * */
        if (in_array($name, self::ATTRIBUTES)) {
            return collect($this->routes)->first();
        }

        return null;
    }

    /**
     * @return DatabaseNotification
     */
    public function notifications()
    {
        return new DatabaseNotification();
    }
}
