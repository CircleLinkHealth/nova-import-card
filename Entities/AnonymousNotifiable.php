<?php
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Entities;


use Illuminate\Notifications\RoutesNotifications;

class AnonymousNotifiable extends \Illuminate\Notifications\AnonymousNotifiable
{
    use RoutesNotifications;
    const ID = 0;

    /**
     * @param $name
     *
     * @return int
     */
    public function __get($name)
    {
        if ($name == 'id') {
            return self::ID;
        }

        /*
         * Structure of $this->routes is:
         * ['channel' => $value e.g. 'mail' => 'test@mail.com']
         * */
        if ($name == 'emr_direct_address' || $name == 'fax' || $name == 'email'){
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