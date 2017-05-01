<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PatientMonthlySummary extends Model
{

    protected $table = 'patient_monthly_summaries';

    protected $fillable = [
        'month_year',
        'ccm_time',
        'no_of_calls',
        'no_of_successful_calls',
        'patient_info_id',
        'is_ccm_complex',
        'approved',
        'rejected',
        'actor_id',
        'billable_problem1',
        'billable_problem2',
        'billable_problem1_code',
        'billable_problem2_code',
    ];

    public static function updateCallInfoForPatient(
        Patient $patient,
        $ifSuccessful
    ) {

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $patient->patientSummaries()->where('month_year', $day_start)->first();

        // set increment var
        $successful_call_increment = 0;
        if ($ifSuccessful) {
            $successful_call_increment = 1;
            // reset call attempts back to 0
            $patient->no_call_attempts_since_last_success = 0;
        } else {
            // add +1 to call attempts
            $patient->no_call_attempts_since_last_success = ($patient->no_call_attempts_since_last_success + 1);
        }
        $patient->save();

        // Determine whether to add to record or not
        if (!$record) {
            $record = new PatientMonthlySummary;
            $record->patient_info_id = $patient->id;
            $record->ccm_time = 0;
            $record->month_year = $day_start;
            $record->no_of_calls = 1;
            $record->no_of_successful_calls = $successful_call_increment;
            $record->save();
        } else {
            $record->no_of_calls = $record->no_of_calls + 1;
            $record->no_of_successful_calls = ($record->no_of_successful_calls + $successful_call_increment);
            $record->save();
        }

        return $record;

    }

    //updates Call info for patient

    public static function updateCCMInfoForPatient(
        Patient $patient,
        $ccmTime
    ) {


        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = $patient->patientSummaries()->where('month_year', $day_start)->first();

        //Detemine whether to add to record or not
        if (!$record) {
            $record = new PatientMonthlySummary;
            $record->patient_info_id = $patient->id;
            $record->ccm_time = $ccmTime;
            $record->month_year = $day_start;
            $record->no_of_calls = 0;
            $record->no_of_successful_calls = 0;
            $record->save();
        } else {
            $record->ccm_time = $ccmTime;
            $record->save();
        }

        return $record;

    }

    public function patient_info()
    {
        return $this->belongsTo(Patient::class);
    }

    public function actor()
    {
        return $this->hasOne(User::class, 'actor_id');
    }

    public function scopeGetCurrent($q)
    {

        return $q->whereMonthYear(Carbon::now()->firstOfMonth()->toDateString());

    }

    public function scopeGetForMonth($q, Carbon $month)
    {

        return $q->whereMonthYear(Carbon::parse($month)->firstOfMonth()->toDateString());

    }


    //Run at beginning of month

    public function getPatientsOver20MinsForPracticeForMonth(
        Practice $practice,
        Carbon $month
    ) {


        $patients = User::where('program_id', $practice->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })->get();

        $count = 0;

        foreach ($patients as $p) {

            if (Activity::totalTimeForPatientForMonth($p->patientInfo, $month, false) > 1199) {
                $count++;
            }

        }

        return $count;

    }

    public static function getPatientQACountForPracticeForMonth(
        Practice $practice,
        Carbon $month
    ){

        $patients = User::where('program_id', $practice->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', '=', 'participant');
            })->get();

        $count['approved'] = 0;
        $count['toQA'] = 0;
        $count['rejected'] = 0;

        foreach ($patients as $p) {

            $ccm = Activity::totalTimeForPatientForMonth($p->patientInfo, $month, false) ;

            if ($ccm < 1200) {

                continue;

            }

            $report = PatientMonthlySummary::where('month_year', $month->firstOfMonth()->toDateString())
                ->where('patient_info_id', $p->patientInfo->id)->first();


            if (!$report) {
                continue;
            }

            $emptyProblemOrCode =
                ($report->billable_problem1_code == '')
                || ($report->billable_problem2_code == '')
                || ($report->billable_problem2 == '')
                || ($report->billable_problem1 == '');

            if (($report->rejected == 0 && $report->approved == 0) || $emptyProblemOrCode) {

                $count['toQA'] += 1;

            } else if ($report->rejected == 1) {

                $count['rejected'] += 1;

            } else if ($report->approved == 1) {

                $count['approved'] += 1;

            }
        }

        return $count;

    }

    public function createCallReportsForCurrentMonth()
    {

        $patients = Patient::all();

        foreach ($patients as $patient) {

            $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));

            PatientMonthlySummary::create([
                'patient_info_id' => $patient->id,
                'ccm_time' => 0,
                'month_year' => $day_start,
                'no_of_calls' => 0,
                'no_of_successful_calls' => 0,

            ]);
        }
    }

    public function updateMonthlyReportForPatient(
        User $patient,
        $ccm_time
    ) {

        $day_start = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $day_end = Carbon::parse(Carbon::now()->endOfMonth()->format('Y-m-d'));

        $info = $patient->patientInfo;

        $no_of_calls = Call::where('outbound_cpm_id', $patient->id)
            ->orWhere('inbound_cpm_id', $patient->id)
            ->where('created_at', '<=', $day_start)
            ->where('created_at', '>=', $day_end)->count();

        $no_of_successful_calls = Call::where('status', 'reached')->where(function ($q) use
        (
            $patient
        ) {
            $q->where('outbound_cpm_id', $patient->id)
                ->orWhere('inbound_cpm_id', $patient->id);
        })
            ->where('created_at', '<=', $day_start)
            ->where('created_at', '>=', $day_end)->count();

        $report = PatientMonthlySummary::where('patient_info_id', $info->id)->where('month_year', $day_start)->first();

        if ($report) {
            $report->ccm_time = $ccm_time;
            $report->no_of_calls = $no_of_calls;
            $report->no_of_successful_calls = $no_of_successful_calls;
            $report->save();
        } else {
            //dd('no report');
        }


    }
}
