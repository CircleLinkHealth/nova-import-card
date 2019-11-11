<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

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
    public function __construct(Carbon $date)
    {
        $this->date  = $date;
        $this->start = $date->copy()->startOfMonth();
        $this->end   = $date->copy()->endOfMonth();
    }

    /**
     * @return mixed
     */
    public static function get(Carbon $date)
    {
        return (new static($date))->categorizedRows();
    }

    /**
     * The goal is to put patients that have been assigned to more than one nurse during the course of the month at the
     * end of the collection which will put at the end of the sheet. Foreach patient id, check the total of rows if
     * patient row exist more than once. Then if it does, take all entries from the collection, add separation at the
     * end of the collection, then put these entries at the end.
     *
     * @return mixed
     */
    private function categorizedRows()
    {
        $data = $this->data();

        $duplicates = [];

        $data = $data->map(function ($calls, $patientId) use (&$duplicates) {
            $callsByNurse = $calls->groupBy('nurse');
            //if there is more than one nurse
            if ($callsByNurse->count() > 1) {
                $duplicates[$patientId] = $callsByNurse;

                return null;
            }

            return $callsByNurse->flatten()->first();
        })->filter();

        $data->push($this->separatingRows());

        foreach ($duplicates as $patient) {
            foreach ($patient as $nurse) {
                $data->push($nurse->first());
            }
        }

        return $data->toArray();
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
            ! $this->start->lt(Carbon::now()->startOfMonth()->startOfDay())
        );
    }

    /**
     * @return $this
     */
    private function patientSummarySubQuery()
    {
        return DB::table('patient_monthly_summaries')
            ->select('patient_id', 'ccm_time', 'bhi_time', 'no_of_calls', 'no_of_successful_calls')
            ->where('month_year', $this->start);
    }

    /**
     * @param bool $getForCurrentMonth
     *
     * @return array
     */
    private function query($getForCurrentMonth)
    {
        return DB::table('calls')
            ->selectRaw('
                 calls.inbound_cpm_id as patient_id, 
                 if (pms.ccm_time is null, \'0\', pms.ccm_time/60) as ccm_time, 
                 if (pms.bhi_time is null, \'0\', pms.bhi_time/60) as bhi_time, 
                 if (pms.no_of_calls is null OR pms.no_of_calls=0, \'0\', pms.no_of_calls) as total_calls,
                 if (pms.no_of_successful_calls is null OR pms.no_of_successful_calls=0, \'0\', pms.no_of_successful_calls) as successful_calls, 
                 nurse_users.display_name as nurse, 
                 practices.display_name as practice')
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
            ->orderBy('patient_id')
            ->get()
            ->groupBy('patient_id');
    }

    private function separatingRows()
    {
        return [
            [null, null, null, null, null, null, null],
            ['Some patients were assigned to more than one nurse. See below:', null, null, null, null, null, null],
            [null, null, null, null, null, null, null],
        ];
    }
}
