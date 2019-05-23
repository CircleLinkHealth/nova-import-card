<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use Illuminate\Support\Collection;

class VariablePayCalculator
{
    /**
     * @var Carbon
     */
    protected $endDate;
    /**
     * @var array
     */
    protected $nurseInfoIds;
    /**
     * @var Carbon
     */
    protected $startDate;

    public function __construct(array $nurseInfoIds, Carbon $startDate, Carbon $endDate)
    {
        $this->nurseInfoIds = $nurseInfoIds;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    /**
     * Get the variable pay collection.
     *
     * @param array  $nurseInfoIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return Collection
     */
    public static function get(array $nurseInfoIds, Carbon $startDate, Carbon $endDate)
    {
        return (new static($nurseInfoIds, $startDate, $endDate))->calculate();
    }

    /**
     * Calculate the variable pay reports.
     *
     * @return Collection
     */
    private function calculate()
    {
        return NurseCareRateLog::whereIn('nurse_id', $this->nurseInfoIds)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select(
                \DB::raw('SUM(increment) as total_time'),
                'ccm_type',
                \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as date"),
                'nurse_id'
                               )
            ->groupBy('nurse_id', 'date', 'ccm_type')
            ->get()
            ->groupBy(['nurse_id', 'date', 'ccm_type']);
    }
}
