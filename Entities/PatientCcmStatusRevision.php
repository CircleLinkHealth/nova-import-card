<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Entries for the db table are created from SQL triggers existing on the patient_info table.
 *
 * @property int                                              $id
 * @property int|null                                         $patient_info_id
 * @property int|null                                         $patient_user_id
 * @property string|null                                      $action
 * @property string|null                                      $old_value
 * @property string|null                                      $new_value
 * @property \Illuminate\Support\Carbon                       $created_at
 * @property \CircleLinkHealth\Customer\Entities\Patient|null $patient
 * @property \CircleLinkHealth\Customer\Entities\User|null    $patientUser
 * @method   static                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision newModelQuery()
 * @method   static                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision newQuery()
 * @method   static                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision ofDate(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate = null)
 * @method   static                                           \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientCcmStatusRevision query()
 * @mixin \Eloquent
 */
class PatientCcmStatusRevision extends Model
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    
    protected $fillable = [];

    protected $table = 'patient_ccm_status_revisions';

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_info_id');
    }

    public function patientUser()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function scopeOfDate($query, Carbon $startDate, Carbon $endDate = null)
    {
        $startDate = $startDate->copy()->startOfDay();
        $endDate   = $endDate ? $endDate->copy()->endOfDay() : $startDate->copy()->endOfDay();

        return $query->where([
            ['created_at', '>=', $startDate],
            ['created_at', '<=', $endDate],
        ]);
    }
}
