<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CareAmbassador.
 *
 * @property int                                                               $id
 * @property int                                                               $user_id
 * @property int|null                                                          $hourly_rate
 * @property int                                                               $speaks_spanish
 * @property \Carbon\Carbon|null                                               $created_at
 * @property \Carbon\Carbon|null                                               $updated_at
 * @property \App\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection $logs
 * @property \App\User                                                         $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereSpeaksSpanish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUserId($value)
 * @mixin \Eloquent
 */
class CareAmbassador extends \App\BaseModel
{
    protected $fillable = [
        'user_id',
        'hourly_rate',
        'speaks_spanish',
    ];

    public function logs()
    {
        return $this->hasMany(CareAmbassadorLog::class, 'enroller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
