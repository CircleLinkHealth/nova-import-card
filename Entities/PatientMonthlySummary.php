<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Traits\HasChargeableServices;
use CircleLinkHealth\SharedModels\Entities\Problem;
use CircleLinkHealth\TimeTracking\Entities\Activity;
use Illuminate\Support\Facades\DB;

/**
 * CircleLinkHealth\Customer\Entities\PatientMonthlySummary.
 *
 * @property int $id
 * @property int $patient_id
 * @property int $ccm_time
 * @property int $bhi_time
 * @property \Carbon\Carbon $month_year
 * @property int $no_of_calls
 * @property int $no_of_successful_calls
 * @property string $billable_problem1
 * @property string $billable_problem1_code
 * @property string $billable_problem2
 * @property string $billable_problem2_code
 * @property int $approved
 * @property int $rejected
 * @property int|null $actor_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $total_time
 * @property \CircleLinkHealth\Customer\Entities\User $actor
 * @property \CircleLinkHealth\Customer\Entities\Patient $patient_info
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     getCurrent()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     getForMonth(\Carbon\Carbon $month)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereActorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereBillableProblem1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereBillableProblem1Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereBillableProblem2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereBillableProblem2Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereCcmTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereMonthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereNoOfCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereNoOfSuccessfulCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     wherePatientInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $closed_ccm_status
 * @property int|null $problem_1
 * @property int|null $problem_2
 * @property int $is_ccm_complex
 * @property int|null $needs_qa
 * @property \CircleLinkHealth\SharedModels\Entities\Problem|null $billableProblem1
 * @property \CircleLinkHealth\SharedModels\Entities\Problem|null $billableProblem2
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection
 *     $billableProblems
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection
 *     $chargeableServices
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 * @property \Illuminate\Database\Eloquent\Collection|\CircleLinkHealth\Revisionable\Entities\Revision[]
 *     $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     hasServiceCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereBhiTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereClosedCcmStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereIsCcmComplex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereNeedsQa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     wherePatientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereProblem1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereProblem2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\PatientMonthlySummary
 *     whereTotalTime($value)
 * @property-read int|null $billable_problems_count
 * @property-read int|null $chargeable_services_count
 * @property-read int|null $revision_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CCD\Problem[] $attestedProblems
 * @property-read int|null $attested_problems_count
 */
class PatientMonthlySummary extends BaseModel
{
    use HasChargeableServices;

    const DATE_ATTESTED_CONDITIONS_ENABLED = '2020-02-01';

    protected $dates = [
        'month_year',
    ];

    protected $fillable = [
        'month_year',
        'total_time',
        'ccm_time',
        'bhi_time',
        'no_of_calls',
        'no_of_successful_calls',
        'patient_id',
        'approved',
        'rejected',
        'needs_qa',
        'actor_id',
        'problem_1', //@todo: Deprecate in favor of billableProblems()
        'problem_2', //@todo: Deprecate in favor of billableProblems()
        'billable_problem1', //@todo: Deprecate in favor of billableProblems()
        'billable_problem1_code', //@todo: Deprecate in favor of billableProblems()
        'billable_problem2', //@todo: Deprecate in favor of billableProblems()
        'billable_problem2_code', //@todo: Deprecate in favor of billableProblems()
    ];

    public function actor()
    {
        return $this->hasOne(User::class, 'actor_id');
    }

    public function attestedProblems()
    {
        return $this->belongsToMany(Problem::class, 'call_problems', 'patient_monthly_summary_id', 'ccd_problem_id');
    }

    public function getAttestedProblemsForReport()
    {
        if (Carbon::parse($this->month_year)->lt(Carbon::parse(PatientMonthlySummary::DATE_ATTESTED_CONDITIONS_ENABLED))) {
            $problems = collect([$this->billableProblem1, $this->billableProblem2])->filter();
        } else {
            $problems = $this->attestedProblems;
        }

        return ! $problems->isEmpty()
            ? $problems->transform(function (Problem $problem) {
                return $problem->icd10Code();
            })->filter()->implode(', ')
            : 'N/A';
    }

    public function attachBillableProblem($problemId, $name, $icd10Code, $type = 'ccm')
    {
        return $this->billableProblems()
                    ->attach(
                        $problemId,
                        [
                            'type'        => $type,
                            'name'        => $name,
                            'icd_10_code' => $icd10Code,
                        ]
                    );
    }

    public function billableBhiProblems()
    {
        return $this->billableProblems()->where('type', '=', 'bhi');
    }

    /**
     * @todo: Deprecate in favor of billableProblems()
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billableProblem1()
    {
        return $this->belongsTo(Problem::class, 'problem_1');
    }

    /**
     * @todo: Deprecate in favor of billableProblems()
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function billableProblem2()
    {
        return $this->belongsTo(Problem::class, 'problem_2');
    }

    public function billableProblems()
    {
        return $this->belongsToMany(Problem::class, 'patient_summary_problems', 'patient_summary_id')
                    ->withPivot('name', 'icd_10_code', 'type')
                    ->withTimestamps();
    }

    public function createCallReportsForCurrentMonth()
    {
        $monthYear = Carbon::now()->startOfMonth()->toDateString();

        $patients = User::select('id')->ofType('participant')
                        ->get()
                        ->map(
                            function ($patient) use ($monthYear) {
                                PatientMonthlySummary::create(
                                    [
                                        'patient_id'             => $patient->id,
                                        'ccm_time'               => 0,
                                        'month_year'             => $monthYear,
                                        'no_of_calls'            => 0,
                                        'no_of_successful_calls' => 0,
                                    ]
                                );
                            }
                        );
    }

    public static function getPatientQACountForPracticeForMonth(
        Practice $practice,
        Carbon $month
    ) {
        $patients = User::where('program_id', $practice->id)
                        ->whereHas(
                            'roles',
                            function ($q) {
                                $q->where('name', '=', 'participant');
                            }
                        )->get();

        $count['approved'] = 0;
        $count['toQA']     = 0;
        $count['rejected'] = 0;

        foreach ($patients as $p) {
            $ccm = Activity::totalTimeForPatientForMonth($p->patientInfo, $month, false);

            if ($ccm < 1200) {
                continue;
            }

            $report = PatientMonthlySummary::where('month_year', $month->firstOfMonth()->toDateString())
                                           ->where('patient_id', $p->id)->first();

            if ( ! $report) {
                continue;
            }

            $emptyProblemOrCode = ('' == $report->billable_problem1_code)
                                  || ('' == $report->billable_problem2_code)
                                  || ('' == $report->billable_problem2)
                                  || ('' == $report->billable_problem1);

            if ((0 == $report->rejected && 0 == $report->approved) || $emptyProblemOrCode) {
                ++$count['toQA'];
            } elseif (1 == $report->rejected) {
                ++$count['rejected'];
            } elseif (1 == $report->approved) {
                ++$count['approved'];
            }
        }

        return $count;
    }

    //Run at beginning of month

    public function getPatientsOver20MinsForPracticeForMonth(
        Practice $practice,
        Carbon $month
    ) {
        $patients = User::where('program_id', $practice->id)
                        ->whereHas(
                            'roles',
                            function ($q) {
                                $q->where('name', '=', 'participant');
                            }
                        )->get();

        $count = 0;

        foreach ($patients as $p) {
            if (Activity::totalTimeForPatientForMonth($p->patientInfo, $month, false) > 1199) {
                ++$count;
            }
        }

        return $count;
    }

    public function bhiAttestedProblems()
    {
        return $this->attestedProblems->where('cpmProblem.is_behavioral', '=', true);
    }

    public function ccmAttestedProblems()
    {
        return $this->attestedProblems->where('cpmProblem.is_behavioral', '=', false);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Set the billing details to null.
     */
    public function reset()
    {
        $this->approved               = null;
        $this->rejected               = null;
        $this->needs_qa               = null;
        $this->actor_id               = null;
        $this->problem_1              = null;
        $this->problem_2              = null;
        $this->billable_problem1      = null;
        $this->billable_problem1_code = null;
        $this->billable_problem2      = null;
        $this->billable_problem2_code = null;
    }

    public function scopeGetCurrent($q)
    {
        return $q->whereMonthYear(Carbon::now()->firstOfMonth()->toDateString());
    }

    public function scopeGetForMonth($q, Carbon $month)
    {
        return $q->whereMonthYear(Carbon::parse($month)->firstOfMonth()->toDateString());
    }

    /**
     * Get how much time (in seconds) was contributed towards this patient's billable time by CLH Care Coaches.
     *
     * @return int
     */
    public function timeFromClhCareCoaches(): int
    {
        return (int)Activity::createdInMonth($this->month_year, 'performed_at')
                            ->where('patient_id', $this->patient_id)
                            ->whereHas('provider', function ($q) {
                                $q->ofType('care-center');
                            })->sum('duration');
    }

    public static function updateCCMInfoForPatient(
        $userId,
        $ccmTime
    ) {
        $dayStart = Carbon::now()->startOfMonth()->toDateString();

        return PatientMonthlySummary::updateOrCreate(
            [
                'patient_id' => $userId,
                'month_year' => $dayStart,
            ],
            [
                'ccm_time' => $ccmTime,
            ]
        );
    }

    /**
     * @param $userId
     * @param Carbon $month
     */
    public static function createFromPatient($userId, Carbon $month){
        //just in case.
        $month->startOfMonth();

        $summary = PatientMonthlySummary::where('patient_id', '=', $userId)
                                        ->orderBy('id', 'desc')->first();

        //if we have already summary for this month, then we skip this
        if ($summary && $month->isSameMonth($summary->month_year)) {
            return;
        }

        if ($summary) {
            //clone record
            $newSummary = $summary->replicate();
        } else {
            $newSummary = new self();
            $newSummary->patient_id = $userId;
        }

        $newSummary->month_year = $month;
        $newSummary->total_time = 0;
        $newSummary->ccm_time = 0;
        $newSummary->bhi_time = 0;
        $newSummary->no_of_calls = 0;
        $newSummary->no_of_successful_calls = 0;
        $newSummary->approved = 0;
        $newSummary->rejected = 0;
        $newSummary->actor_id = null;
        $newSummary->needs_qa = null;
        $newSummary->save();
    }


    public function syncAttestedProblems(Array $attestedProblems)
    {
        //remove summary id without detaching. We may still need the association of the problem with the call
        $this->attestedProblems()->update(['call_problems.patient_monthly_summary_id' => null]);

        DB::table('call_problems')
          ->whereNull('call_id')
          ->whereNull('patient_monthly_summary_id')
          ->delete();

        $this->attestedProblems()->attach($attestedProblems);
    }
}

