<?php

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Class AwvAppointment.
 *
 * @property int $user
 * @property string $type
 * @property Carbon $appointment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AwvAppointment extends BaseModel
{
    protected $table = 'awv_appointments';

    protected $dates = [
        'appointment',
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'type',
        'appointment',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
