<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog;

/**
 * App\CareAmbassador.
 *
 * @property int                                                               $id
 * @property int                                                               $user_id
 * @property int|null                                                          $hourly_rate
 * @property int                                                               $speaks_spanish
 * @property \Carbon\Carbon|null                                               $created_at
 * @property \Carbon\Carbon|null                                               $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection $logs
 * @property \CircleLinkHealth\Customer\Entities\User                          $user
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereCreatedAt($value)
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereHourlyRate($value)
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereId($value)
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereSpeaksSpanish($value)
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUpdatedAt($value)
 * @method   static                                                            \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador whereUserId($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CareAmbassador query()
 * @property int|null                                                                                    $logs_count
 * @property int|null                                                                                    $revision_history_count
 */
class CareAmbassador extends \CircleLinkHealth\Core\Entities\BaseModel
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
