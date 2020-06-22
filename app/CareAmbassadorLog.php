<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CareAmbassador;
use CircleLinkHealth\Customer\Entities\Practice;

/**
 * App\CareAmbassadorLog.
 *
 * @property int                      $id
 * @property int|null                 $enroller_id
 * @property string                   $day
 * @property int                      $no_enrolled
 * @property int                      $no_rejected
 * @property int                      $no_soft_rejected
 * @property int                      $no_utc
 * @property int                      $total_calls
 * @property int                      $total_time_in_system
 * @property \Carbon\Carbon|null      $created_at
 * @property \Carbon\Carbon|null      $updated_at
 * @property \App\CareAmbassador|null $enroller
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereEnrollerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoEnrolled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereTotalCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereTotalTimeInSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int|null                                                                                    $practice_id
 * @property \CircleLinkHealth\Customer\Entities\Practice                                                $practice
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog whereNoSoftRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareAmbassadorLog wherePracticeId($value)
 *
 * @property int|null $revision_history_count
 */
class CareAmbassadorLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $fillable = [
        'enroller_id',
        'day',
        'no_enrolled',
        'no_rejected',
        'no_soft_rejected',
        'no_utc',
        'total_calls',
        'total_time_in_system',
    ];

//    public function totalUniquePatientsCalled(){
//
//        return Enrollee::where('care_ambassador_id', $this->care_ambassador_id)->count();
//
//    }

    public static function createOrGetLogs($enroller_id, Carbon $date = null)
    {
        if ( ! $date) {
            $date = Carbon::now();
        }
        $date = $date->format('Y-m-d');

        $report = self
                    ::where('enroller_id', $enroller_id)
                        ->where('day', $date)
                        ->first();

        if (null == $report) {
            return self
                   ::create([
                       'enroller_id' => $enroller_id,
                       'day'         => $date,
                   ]);
        }

        return $report;
    }

    public function enroller()
    {
        return $this->belongsTo(CareAmbassador::class, 'enroller_id');
    }

    public function practice()
    {
        return $this->hasOne(Practice::class, 'practice_id');
    }
}
