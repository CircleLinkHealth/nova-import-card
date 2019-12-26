<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/21/19
 * Time: 4:39 AM
 */

namespace App\Contracts;


interface FaxableNotification
{
    public function toFax($notifiable = null) : array;
}