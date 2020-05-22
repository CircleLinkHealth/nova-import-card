<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutgoingSms extends Model
{
    protected $fillable = [
        'sender_user_id',
        'receiver_phone_number',
        'message',
    ];
}
