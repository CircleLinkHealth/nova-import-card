<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\Appointment.
 *
 * @property int                 $id
 * @property int                 $patient_id
 * @property int                 $author_id
 * @property int|null            $provider_id
 * @property string              $date
 * @property string              $time
 * @property string              $status
 * @property string              $comment
 * @property int                 $was_completed
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string              $type
 * @property \App\User           $author
 * @property \App\User           $patient
 * @property \App\User|null      $provider
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Appointment whereWasCompleted($value)
 * @mixin \Eloquent
 */
class Appointment extends \App\BaseModel
{
    protected $fillable = [
        'patient_id',
        'author_id',
        'provider_id',
        'was_completed',
        'type',
        'date',
        'time',
        'comment',
        'created_at',
        'updated_at',
    ];
    protected $table = 'appointments';

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
