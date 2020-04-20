<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\TwoFA\Entities\AuthyUser.
 *
 * @property int                                                                                         $id
 * @property int|null                                                                                    $user_id
 * @property int                                                                                         $is_authy_enabled
 * @property string|null                                                                                 $authy_id
 * @property string|null                                                                                 $authy_method
 * @property string|null                                                                                 $country_code
 * @property string|null                                                                                 $phone_number
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User|null                                               $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereAuthyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereAuthyMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereIsAuthyEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\TwoFA\Entities\AuthyUser whereUserId($value)
 * @mixin \Eloquent
 *
 * @property int|null $revision_history_count
 */
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
