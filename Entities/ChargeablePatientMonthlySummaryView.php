<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Entities;

use CircleLinkHealth\Core\Entities\SqlViewModel;
use CircleLinkHealth\TimeTracking\Traits\DateScopesTrait;

/**
 * CircleLinkHealth\CcmBilling\Entities\ChargeablePatientMonthlySummaryView.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView createdInMonth(\Carbon\Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView createdOn(\Carbon\Carbon $date, $field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView createdThisMonth($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView createdToday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView createdYesterday($field = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChargeablePatientMonthlySummaryView query()
 * @mixin \Eloquent
 * @property int                             $id
 * @property int                             $patient_user_id
 * @property int|null                        $chargeable_service_id
 * @property string                          $chargeable_month
 * @property int|null                        $actor_id
 * @property int                             $is_fulfilled
 * @property int                             $requires_patient_consent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null                     $chargeable_service_code
 * @property int|null                        $no_of_calls
 * @property int|null                        $no_of_successful_calls
 * @property string|null                     $total_time
 */
class ChargeablePatientMonthlySummaryView extends SqlViewModel
{
    use DateScopesTrait;

    protected $table = 'chargeable_patient_monthly_summaries_view';
}
