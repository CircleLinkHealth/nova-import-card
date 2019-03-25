<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int            $id
 * @property int            $user_id
 * @property string         $older_password
 * @property string         $old_password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserPasswordsHistory extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'user_id',
        'older_password',
        'old_password',
        'created_at',
        'updated_at',
    ];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_password_history';
}
