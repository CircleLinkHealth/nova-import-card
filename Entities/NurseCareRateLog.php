<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\SharedModels\Entities\Activity;

/**
 * CircleLinkHealth\Customer\Entities\NurseCareRateLog.
 *
 * @property int                                                   $id
 * @property int|null                                              $time_before
 * @property bool|null                                             $is_successful_call
 * @property int                                                   $nurse_id
 * @property int|null                                              $activity_id
 * @property int|null                                              $patient_user_id
 * @property string                                                $ccm_type
 * @property int                                                   $increment
 * @property bool|null                                             $is_behavioral
 * @property \Carbon\Carbon|null                                   $created_at
 * @property \Carbon\Carbon|null                                   $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\Activity|null $activity
 * @property \CircleLinkHealth\Customer\Entities\Nurse             $nurse
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereActivityId($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCcmType($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCreatedAt($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereId($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereIncrement($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereNurseId($value)
 * @method   static                                                \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog
 *     newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog query()
 * @property int|null                        $revision_history_count
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog whereIsSuccessfulCall($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog wherePatientUserId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog whereTimeBefore($value)
 * @property int|null                        $chargeable_service_id
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog whereChargeableServiceId($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\NurseCareRateLog whereIsBehavioral($value)
 * @property \Illuminate\Support\Carbon|null $performed_at
 */
class NurseCareRateLog extends \CircleLinkHealth\Core\Entities\BaseModel
{
    protected $dates = [
        'performed_at',
    ];

    protected $fillable = [
        'nurse_id',
        'activity_id',
        'ccm_type',
        'increment',
        'created_at',
        'patient_user_id',
        'time_before',
        'is_successful_call',
        'is_behavioral',
        'performed_at',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
}
