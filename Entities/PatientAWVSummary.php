<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * CircleLinkHealth\Customer\Entities\PatientAWVSummary.
 *
 * @property int                                      $id
 * @property int                                      $user_id
 * @property int                                      $year
 * @property int                                      $is_initial_visit
 * @property int                                      $is_billable
 * @property string|null                              $billable_at
 * @property \Illuminate\Support\Carbon|null          $created_at
 * @property \Illuminate\Support\Carbon|null          $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereBillableAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereIsBillable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereIsInitialVisit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientAWVSummary whereYear($value)
 * @mixin \Eloquent
 */
class PatientAWVSummary extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'is_initial_visit',
        'is_billable',
        'billable_at',
    ];
    protected $table = 'patient_awv_summaries';

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
