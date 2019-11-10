<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Circlelinkhealth\GeneratePatientCallDataCsv;

use App\CallView;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PatientCallData
{
    protected $date;

    public function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public static function get(Carbon $date)
    {
        return (new static($date))->getForDate();
    }

    private function forCurrentMonth()
    {
        return CallView::select('patient_id', 'ccm_time', 'bhi_time', 'no_of_successful_calls', 'nurse', 'practice')
            ->whereStatus('scheduled')
            ->get()
            ->toArray();
    }

    private function forPastMonths()
    {
        //needs fix
        try {
            return DB::table('calls')
                ->select(
                         DB::raw('calls.inbound_cpm_id as patient_id')
//                         DB::raw(
//                             'pms.ccm_time'
//                         ),
//                         DB::raw(
//                             "pms.bhi_time"
//                         ),
//                         DB::raw(
//                             'pms.no_of_successful_calls'
//                         ),
//                         DB::raw(
//                             'nurse_users.display_name as nurse'
//                         ),
//                         DB::raw(
//                             'practices.display_name as practice'
//                         )
                     )
//                     ->leftJoin('users as patient_users', 'patient_users.id', '=', 'calls.inbound_cpm_id')
//                     ->leftJoin('users as nurse_users', 'nurse_users.id', '=', 'calls.outbound_cpm_id')
//                     ->leftJoinSub($this->patientSummarySubquery(), 'pms', function ($join) {
//                         $join->on('calls.inbound_cpm_id', '=', 'pms.patient_id');
//                     })
//                     ->leftJoin('patient_info', 'patient_users.id', '=', 'patient_info.user_id')
//                     ->leftJoin('practices', 'patient_users.program_id', '=', 'practices.id')
                ->get();
//                     ->toArray();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    private function getForDate()
    {
        if ($this->date->lt(Carbon::now()->startOfMonth()->startOfDay())) {
            return $this->forPastMonths();
        }

        return $this->forCurrentMonth();
    }

    private function patientSummarySubquery()
    {
        return DB::table('patient_monthly_summaries')
            ->select('patient_id', 'ccm_time', 'bhi_time', 'no_of_successful_calls')
            ->where('month_year', $this->date->copy()->startOfMonth());
    }
}
