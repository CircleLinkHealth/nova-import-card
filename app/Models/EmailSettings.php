<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmailSettings
 *
 * @property int $id
 * @property int $user_id
 * @property string $frequency
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\EmailSettings whereUserId($value)
 * @mixin \Eloquent
 */
class EmailSettings extends \App\BaseModel
{
    public $fillable = [
        'user_id',
        'frequency'
    ];

    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const MWF = 'm/w/f';

    public $attributes = [
        'frequency' => EmailSettings::DAILY,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
