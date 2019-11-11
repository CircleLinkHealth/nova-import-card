<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\GeneratePatientCallDataCsv;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientCallData
{
    /**
     * @var Carbon
     */
    protected $end;
    /**
     * @var Carbon
     */
    protected $start;

    /**
     * PatientCallData constructor.
     */
    public function __construct(Carbon $start, Carbon $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    /**
     * @return mixed
     */
    public static function get(Carbon $start, Carbon $end)
    {
        return (new static($start, $end))->data();
    }

    /**
     * If we need results for current month, we need to include calls with called_date AND scheduled_date within our
     * time frame.
     *
     * @return array
     */
    private function data()
    {
        return $this->query(
            $this->end->gt(Carbon::now()->startOfMonth()->startOfDay()) &&
            $this->end->lte(Carbon::now()->endOfMonth()->endOfDay())
        );
    }

    /**
     * @return $this
     */
    private function patientSummarySubQuery()
    {
        return DB::table('patient_monthly_summaries')
            ->select('patient_id', 'ccm_time', 'bhi_time', 'no_of_successful_calls')
            ->where('month_year', $this->date->copy()->startOfMonth());
    }

    /**
     * @param $getforCurrentMonth
     * @param mixed $getForCurrentMonth
     *
     * @return array
     */
    private function query($getForCurrentMonth)
    {
        return DB::table('calls')
            ->selectRaw('calls.inbound_cpm_id as patient_id, pms.ccm_time, pms.bhi_time, pms.no_of_successful_calls, nurse_users.display_name as nurse, practices.display_name as practice')
            ->leftJoin('users as patient_users', 'patient_users.id', '=', 'calls.inbound_cpm_id')
            ->leftJoin('users as nurse_users', 'nurse_users.id', '=', 'calls.outbound_cpm_id')
            ->leftJoinSub($this->patientSummarySubQuery(), 'pms', function ($join) {
                     $join->on('calls.inbound_cpm_id', '=', 'pms.patient_id');
                 })
            ->leftJoin('practices', 'patient_users.program_id', '=', 'practices.id')
            ->whereRaw(
                     "(DATE(calls.called_date)  >= DATE('{$this->start}') AND DATE(calls.called_date) <= DATE('{$this->end}'))"
                 )
            ->when($getForCurrentMonth, function ($query) {
                     $query->whereRaw("(DATE(calls.scheduled_date)  >= DATE('{$this->start}') AND DATE(calls.scheduled_date) <= DATE('{$this->end}'))");
                 })
            ->get()
            ->toArray();
    }
}
