<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\Core\Traits\DateScopesTrait;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlyTime.
 *
 * @property int                             $patient_user_id
 * @property int|null                        $chargeable_service_id
 * @property \Illuminate\Support\Carbon|null $chargeable_month
 * @property string|null                     $total_time
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdInMonth(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdOn(\Carbon\Carbon $date, string $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdOnIfNotNull(?\Carbon\Carbon $date = null, $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdThisMonth(string $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdToday(string $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime createdYesterday(string $field = 'created_at')
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime newModelQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime newQuery()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlyTime query()
 * @mixin \Eloquent
 * @property ChargeableService|null $chargeableService
 * @property User                   $patient
 */
class ChargeablePatientMonthlyTime extends SqlViewModel
{
    use DateScopesTrait;

    protected $dates = [
        'chargeable_month',
    ];

    protected $table = 'chargeable_patient_monthly_times_view';

    public function chargeableService()
    {
        return $this->belongsTo(ChargeableService::class, 'chargeable_service_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }
}
