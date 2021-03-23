<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Traits\DateScopesTrait;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property int|null                                                                                    $actor_id
 * @property string|null                                                                                 $status
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus query()
 * @mixin \Eloquent
 * @property User                                                                                                          $patientUser
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdInMonth(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdOn(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdOnIfNotNull(?\Carbon\Carbon $date = null, $field = 'created_at')
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdThisMonth(string $field = 'created_at')
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdToday(string $field = 'created_at')
 * @method   static                                                                                                        \Illuminate\Database\Eloquent\Builder|PatientMonthlyBillingStatus createdYesterday(string $field = 'created_at')
 * @property \CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime[]|\Illuminate\Database\Eloquent\Collection $chargeableMonthlyTime
 * @property int|null                                                                                                      $chargeable_monthly_time_count
 */
class PatientMonthlyBillingStatus extends BaseModel
{
    use DateScopesTrait;

    const APPROVED = 'approved';
    const NEEDS_QA = 'needs_qa';
    const REJECTED = 'rejected';

    protected $dates = [
        'chargeable_month',
    ];

    protected $fillable = [
        'patient_user_id',
        'chargeable_month',
        'actor_id',
        'status',
    ];

    protected $table = 'patient_monthly_billing_statuses';

    public function chargeableMonthlyTime()
    {
        return $this->hasMany(ChargeablePatientMonthlyTime::class, 'patient_user_id', 'patient_user_id');
    }

    public function isApproved(): bool
    {
        return self::APPROVED === $this->status;
    }

    public function isRejected(): bool
    {
        return self::REJECTED === $this->status;
    }

    public function needsQA(): bool
    {
        return self::NEEDS_QA === $this->status;
    }

    public function patientUser()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}