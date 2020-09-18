<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Metrics;

use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\PracticeRoleUser;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Revisionable\Entities\Revision;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Value;

class PatientsOverTargetCcmTime extends Value
{
    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return \DateInterval|\DateTimeInterface|float|int
     */
    public function cacheFor()
    {
        return now()->addMinutes(5);
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(Request $request)
    {
        $revisionsTable = (new Revision())->getTable();

        return $this->count(
            $request,
            $this->patientsOverTargetQuery('ccm_time'),
            null,
            "$revisionsTable.created_at"
        );
    }

    public function patientsOverTargetQuery($key)
    {
        $summariesTable        = (new PatientMonthlySummary())->getTable();
        $revisionsTable        = (new Revision())->getTable();
        $practiceRoleUserTable = (new PracticeRoleUser())->getTable();
        $usersTable            = (new User())->getTable();
        $activePracticesIds    = Practice::activeBillable()->pluck('id')->all();

        return Revision::query()
            ->where('revisionable_type', PatientMonthlySummary::class)
            ->where('key', $key)
            ->where(
                'old_value',
                '<',
                CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS
            )
            ->where(
                'new_value',
                '>=',
                CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS
            )
            ->leftJoin($summariesTable, "$revisionsTable.revisionable_id", '=', "$summariesTable.id")
            ->leftJoin(
                $usersTable,
                "$usersTable.id",
                '=',
                "$summariesTable.patient_id"
            )
            ->whereIn("$usersTable.program_id", $activePracticesIds);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            'MTD' => 'Month To Date',
            1     => '24 hours',
            2     => '48 hours',
            7     => '7 Days',
            60    => '60 Days',
            365   => '365 Days',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'patients-over-target-ccm-time';
    }
}
