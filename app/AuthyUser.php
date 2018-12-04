<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

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
