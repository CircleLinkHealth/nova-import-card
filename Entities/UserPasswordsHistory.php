<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\Entities\UserPasswordsHistory.
 *
 * @property int            $id
 * @property int            $user_id
 * @property string         $older_password
 * @property string         $old_password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereOldPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereOlderPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereUserId($value)
 * @mixin \Eloquent
 *
 * @property int         $force_change
 * @property string|null $force_change_reason
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereForceChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\UserPasswordsHistory whereForceChangeReason($value)
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
