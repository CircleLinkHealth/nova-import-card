<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PatientMonthlySummary extends Model
{
   
    protected $table = 'patient_monthly_summaries';

    protected $fillable = ['month_year, ccm_time, no_of_calls, no_of_successful_calls', 'patient_info_id'];

    public function patient_info()
    {
        return $this->belongsTo(PatientInfo::class);
    }

    //updates Call info for patient
    public static function updateCallInfoForPatient(PatientInfo $patient, $ifSuccessful){

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));


        $record = $patient->patientSummaries()->where('month_year',$day_start)->get();

        //Determine whether to add to record or not

        $successful_call_increment = 0;

        if($ifSuccessful){
            $successful_call_increment = 1;
        }

        if($record && $record->count() < 1){

            $record = PatientMonthlySummary::create([
                'patient_info_id' => $patient->id,
                'ccm_time' =>  0,
                'month_year' => $day_start,
                'no_of_calls' => 1,
                'no_of_successful_calls' => $successful_call_increment

            ]);


        } else {
            
            if($record->count() == 1){

                $record->no_of_calls = $record->no_of_calls + 1;
                $record->no_of_successful_calls = $record->no_of_calls + $successful_call_increment;
                $record->save();

            } else {

                $record[0]->no_of_calls = $record[0]->no_of_calls + 1;
                $record[0]->no_of_successful_calls = $record[0]->no_of_calls + $successful_call_increment;
                $record[0]->save();

            }

        }



    }

    public static function updateCCMInfoForPatient(PatientInfo $patient, $ccmTime){

        $record = $patient->patientSummaries();
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));

        //Detemine whether to add to record or not
        if($record){

            PatientMonthlySummary::create([
                'patient_info_id' => $patient->id,
                'ccm_time' =>  $ccmTime,
                'month_year' => $day_start,
                'no_of_calls' => 0,
                'no_of_successful_calls' => 0

            ]);

        } else {

            $record->ccm_time = $ccmTime;
            $record->save();

        }

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

        if($report) {
            $report->ccm_time = $ccm_time;
            $report->no_of_calls = $no_of_calls;
            $report->no_of_successful_calls = $no_of_successful_calls;
            $report->save();
        } else {
            //dd('no report');
        }


    }
}
