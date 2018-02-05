<?php

namespace App;

use App\Models\CCD\Problem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PatientMonthlySummary
 *
 * @property int $id
 * @property int $patient_id
 * @property int $ccm_time
 * @property \Carbon\Carbon $month_year
 * @property int $no_of_calls
 * @property int $no_of_successful_calls
 * @property string $billable_problem1
 * @property string $billable_problem1_code
 * @property string $billable_problem2
 * @property string $billable_problem2_code
 * @property int $is_ccm_complex
 * @property int $approved
 * @property int $rejected
 * @property int|null $actor_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $actor
 * @property-read \App\Patient $patient_info
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary getCurrent()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary getForMonth(\Carbon\Carbon $month)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereBillableProblem1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereBillableProblem1Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereBillableProblem2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereBillableProblem2Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereCcmTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereIsCcmComplex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereMonthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereNoOfCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereNoOfSuccessfulCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary wherePatientInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PatientMonthlySummary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PatientMonthlySummary extends \App\BaseModel
{
    protected $dates = [
        'month_year'
    ];

    protected $fillable = [
        'month_year',
        'ccm_time',
        'no_of_calls',
        'no_of_successful_calls',
        'patient_id',
        'is_ccm_complex',
        'approved',
        'rejected',
        'actor_id',
        'problem_1',
        'problem_2',
    ];

    public static function updateCallInfoForPatient(
        Patient $patient,
        $ifSuccessful
    ) {

        // get record for month
        $day_start = Carbon::parse(Carbon::now()->firstOfMonth())->format('Y-m-d');
        $record = PatientMonthlySummary::where('patient_id', $patient->user_id)
                                       ->where('month_year', $day_start)
                                       ->first();

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
            $record->patient_id = $patient->user_id;
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
        $userId,
        $ccmTime
    ) {
        $dayStart = Carbon::now()->startOfMonth()->toDateString();

        $record = PatientMonthlySummary::updateOrCreate([
            'patient_id' => $userId,
            'month_year' => $dayStart
        ], [
            'ccm_time' => $ccmTime
        ]);

        if (!$record->problem_1 || !$record->problem_2) {
            $existingRecord = PatientMonthlySummary::where('patient_id', $userId)
                                                   ->where('id', '!=', $record->id)
                                                   ->orderBy('id', 'DESC')
                                                   ->first();

            if ($existingRecord) {
                if ($existingRecord->problem_1 && !$record->problem_1) $record->problem_1 = $existingRecord->problem_1;
                if ($existingRecord->problem_2 && !$record->problem_2) $record->problem_2 = $existingRecord->problem_2;
                $record->save();
            }
        }

        return $record;
    }

    public function patientInfo()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
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

    public function chargeableServices(){
        return $this->morphToMany(  ChargeableService::class, 'chargeable')
                    ->withPivot(['amount'])
                    ->withTimestamps();
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
    ) {

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
                ->where('patient_id', $p->id)->first();


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
        $monthYear = Carbon::now()->startOfMonth()->toDateString();

        $patients = User::select('id')->ofType('participant')
                                      ->get()
                                    ->map(function ($patient) use ($monthYear) {
                                        PatientMonthlySummary::create([
                                            'patient_id' => $patient->id,
                                            'ccm_time' => 0,
                                            'month_year' => $monthYear,
                                            'no_of_calls' => 0,
                                            'no_of_successful_calls' => 0,
                                        ]);
                                    });
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

        $report = PatientMonthlySummary::where('patient_id', $patient->id)->where('month_year', $day_start)->first();

        if ($report) {
            $report->ccm_time = $ccm_time;
            $report->no_of_calls = $no_of_calls;
            $report->no_of_successful_calls = $no_of_successful_calls;
            $report->save();
        } else {
            //dd('no report');
        }
    }

    public function billableProblem1() {
        return $this->belongsTo(Problem::class, 'problem_1');
    }

    public function billableProblem2() {
        return $this->belongsTo(Problem::class, 'problem_2');
    }
}
