<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;

/**
 * CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummary.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $patient_user_id
 * @property int|null                                                                                    $chargeable_service_id
 * @property \Illuminate\Support\Carbon                                                                  $chargeable_month
 * @property int|null                                                                                    $actor_id
 * @property bool                                                                                        $is_fulfilled
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property ChargeableService|null                                                                      $chargeableService
 * @property User                                                                                        $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary query()
 * @mixin \Eloquent
 * @property int    $requires_patient_consent
 * @method   static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method   static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary createdThisMonth($field = 'created_at')
 * @method   static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary createdToday($field = 'created_at')
 * @method   static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummary createdYesterday($field = 'created_at')
 */
class ChargeablePatientMonthlySummary extends BaseModel
{
    use DateScopesTrait;

    protected $casts = [
        'is_fulfilled'             => 'boolean',
        'requires_patient_consent' => 'boolean',
    ];

    protected $dates = [
        'chargeable_month',
    ];
    protected $fillable = [
        'patient_user_id',
        'chargeable_service_id',
        'chargeable_month',
        'actor_id',
        'is_fulfilled',
        'requires_patient_consent',
    ];

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
