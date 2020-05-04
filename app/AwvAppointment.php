<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * Class AwvAppointment.
 *
 * @property int    $user
 * @property string $type
 * @property Carbon $appointment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AwvAppointment extends BaseModel
{
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
    protected $table = 'awv_appointments';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
