<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Models;

use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Models\EmailSettings.
 *
 * @property int                 $id
 * @property int                 $user_id
 * @property string              $frequency
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User           $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUserId($value)
 * @mixin \Eloquent
 */
class EmailSettings extends \CircleLinkHealth\Core\Entities\BaseModel
{
    const DAILY  = 'daily';
    const MWF    = 'm/w/f';
    const WEEKLY = 'weekly';

    public $attributes = [
        'frequency' => EmailSettings::DAILY,
    ];
    public $fillable = [
        'user_id',
        'frequency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
