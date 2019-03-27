<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\User;

class AuthyUser extends BaseModel
{
    protected $fillable = [
        'user_id',
        'is_authy_enabled',
        'authy_id',
        'authy_method',
        'country_code',
        'phone_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
