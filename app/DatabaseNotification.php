<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 11/17/2017
 * Time: 3:54 PM
 */

namespace App;


class DatabaseNotification extends \Illuminate\Notifications\DatabaseNotification
{
    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function attachment()
    {
        return $this->morphTo();
    }
}