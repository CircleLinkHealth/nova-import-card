<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PatientMonthlySummary extends Model
{
   
    protected $table = 'patient_monthly_summaries';

    protected $guarded = ['id'];

    public function patient_info()
    {
        return $this->belongsTo(PatientInfo::class);
    }


    //Run at beginning of month
    public function createCallReportsForCurrentMonth(){

        $patients = PatientInfo::all();

        foreach ($patients as $patient){

            $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
            $day_end = Carbon::parse(Carbon::now()->endOfMonth()->format('Y-m-d'));

            PatientMonthlySummary::create([
                'patient_info_id' => $patient->id,
                'ccm_time' =>  0,
                'month_year' => $day_start,
                'no_of_calls' => 0,
                'no_of_successful_calls' => 0

            ]);
        }
    }

    public function updateMonthlyReportForPatient(User $patient, $ccm_time){

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $day_end = Carbon::parse(Carbon::now()->endOfMonth()->format('Y-m-d'));

        $info = $patient->patientInfo;

        $no_of_calls = Call::where('outbound_cpm_id', $patient->ID)
            ->orWhere('inbound_cpm_id', $patient->ID)
            ->where('created_at', '<=' , $day_start)
            ->where('created_at', '>=' , $day_end)->count();

        $no_of_successful_calls = Call::where('status','reached')->where(function ($q) use ($patient){
            $q->where('outbound_cpm_id', $patient->ID)
                ->orWhere('inbound_cpm_id', $patient->ID);})
            ->where('created_at', '<=' , $day_start)
            ->where('created_at', '>=' , $day_end)->count();

        $report = PatientMonthlySummary::where('patient_info_id', $info->id)->where('month_year', $day_start)->first();

        $report->ccm_time = $ccm_time;
        $report->no_of_calls = $no_of_calls;
        $report->no_of_successful_calls = $no_of_successful_calls;

        dd($no_of_calls);

        $report->save();

    }
}
