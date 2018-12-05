<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\NurseCareRateLog.
 *
 * @property int                 $id
 * @property int                 $nurse_id
 * @property int|null            $activity_id
 * @property string              $ccm_type
 * @property int                 $increment
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \App\Activity|null  $activity
 * @property \App\Nurse          $nurse
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCcmType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereIncrement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereNurseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\NurseCareRateLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NurseCareRateLog extends \App\BaseModel
{
    protected $fillable = ['nurse_id', 'activity_id', 'ccm_type', 'increment', 'created_at'];
    protected $table    = 'nurse_care_rate_logs';

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
}
