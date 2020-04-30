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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * CircleLinkHealth\Customer\Entities\PatientMonthlySummary.
 *
 * @property int                                         $id
 * @property int                                         $patient_id
 * @property int                                         $ccm_time
 * @property int                                         $bhi_time
 * @property \Carbon\Carbon                              $month_year
 * @property int                                         $no_of_calls
 * @property int                                         $no_of_successful_calls
 * @property string                                      $billable_problem1
 * @property string                                      $billable_problem1_code
 * @property string                                      $billable_problem2
 * @property string                                      $billable_problem2_code
 * @property int                                         $approved
 * @property int                                         $rejected
 * @property int|null                                    $actor_id
 * @property \Carbon\Carbon|null                         $created_at
 * @property \Carbon\Carbon|null                         $updated_at
 * @property int                                         $total_time
 * @property \CircleLinkHealth\Customer\Entities\User    $actor
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
 * @property string|null                                          $closed_ccm_status
 * @property int|null                                             $problem_1
 * @property int|null                                             $problem_2
 * @property int                                                  $is_ccm_complex
 * @property int|null                                             $needs_qa
 * @property \CircleLinkHealth\SharedModels\Entities\Problem|null $billableProblem1
 * @property \CircleLinkHealth\SharedModels\Entities\Problem|null $billableProblem2
 * @property \CircleLinkHealth\SharedModels\Entities\Problem[]|\Illuminate\Database\Eloquent\Collection
 *     $billableProblems
 * @property \CircleLinkHealth\Customer\Entities\ChargeableService[]|\Illuminate\Database\Eloquent\Collection
 *     $chargeableServices
 * @property \CircleLinkHealth\Customer\Entities\User $patient
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection
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
 * @property int|null                                                           $billable_problems_count
 * @property int|null                                                           $chargeable_services_count
 * @property int|null                                                           $revision_history_count
 * @property \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection $attestedProblems
 * @property int|null                                                           $attested_problems_count
 */
class PatientMonthlySummary extends BaseModel
{
    use HasChargeableServices;

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

    /**
     * Attach service codes in a summary before the billable process,
     * So that we can perform complex validation on nurse attestation where it's enabled.
     */
    public function attachLastMonthsChargeableServicesIfYouShould(PatientMonthlySummary $lastMonthsSummary = null)
    {
        if ($this->chargeableServices->isNotEmpty()) {
            return;
        }

        if ( ! $lastMonthsSummary) {
            $lastMonthsSummary = PatientMonthlySummary::with('chargeableServices')
                ->where('patient_id', $this->patient_id)
                ->where('month_year', Carbon::now()->startOfMonth()->subMonth())
                ->first();
        }

        if ( ! $lastMonthsSummary || $lastMonthsSummary->chargeableServices->isEmpty()) {
            $patient = $this->patient;

            if ( ! $patient) {
                \Log::critical("PMS with id:{$this->id} does not have Patient attached.");

                return;
            }

            $practice = optional($patient)->primaryPractice;

            if ( ! $practice) {
                \Log::critical("Patient with id:{$patient->id} does not have Practice attached.");

                return;
            }

            /**
             * @var Collection
             */
            $practiceCodes = $practice->chargeableServices->get();

            if ($practiceCodes->isEmpty()) {
                return;
            }

            if (1 === $practiceCodes->count()) {
                $this->chargeableServices()->attach($practiceCodes->first()->id);

                return;
            }

            $patientProblems = $patient->ccdProblems()->with('cpmProblem')->get();

            $practiceBhiCode = $practiceCodes->where('code', ChargeableService::BHI)->first();

            //Opting for this instead of "if patient has 1+ BHI problem and practice has BHI, attach BHI code"
            //There have been cases of Practice having BHI code, and patient having BHI problems,
            //But not a BHI code. Avoiding this here (for patients with no last month summaries case)
            //so that we can nurses being wrongly blocked on attestation
            //If patient does have BHI 20 mins and BHI problems it will get automatically attahed by job using PMS->autoAttestConditionsIfYouShould()
            $patientOnlyHasBhiProblems = $patientProblems->where('cpmProblem.is_behavioral', true)->count() === $patientProblems->count();
            if ($patientOnlyHasBhiProblems && $practiceBhiCode) {
                $this->chargeableServices()->attach($practiceBhiCode->id);
            }

            $practiceCcmCode = $practiceCodes->where('code', ChargeableService::CCM)->first();
            if ($patientProblems->count() >= 2 && $practiceCcmCode) {
                $this->chargeableServices()->attach($practiceCcmCode->id);
            }

            //only check for CCM and BHI here, since they are the only ones making a difference in validation
            //all other codes fall under the default validation (at least 1 problem attested)
            return;
        }

        $chargeableServiceIds = $lastMonthsSummary->chargeableServices->pluck('id')->toArray();

        $this->chargeableServices()->attach($chargeableServiceIds);

        $this->load('chargeableServices');
    }

    public function attestedProblems()
    {
        return $this->belongsToMany(Problem::class, 'call_problems', 'patient_monthly_summary_id', 'ccd_problem_id');
    }

    public function autoAttestConditionsIfYouShould()
    {
        $this->loadMissing('attestedProblems');

        if ($this->unAttestedPcm() || $this->unAttestedCcm()) {
            $this->syncAttestedProblems($this->getCcmProblemsForAutoAttestation());
        }

        if ($this->unAttestedBhi()) {
            $this->syncAttestedProblems($this->getBhiProblemsForAutoAttestation());
        }
    }

    /**
     * @return Collection|static
     */
    public function bhiAttestedProblems()
    {
        if ( ! $this->hasServiceCode(ChargeableService::BHI)) {
            return collect([]);
        }

        return $this->attestedProblems->where('cpmProblem.is_behavioral', '=', true);
    }

    public function billableBhiProblems()
    {
        return $this->billableProblems()->where('type', '=', 'bhi');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @todo: Deprecate in favor of billableProblems()
     */
    public function billableProblem1()
    {
        return $this->belongsTo(Problem::class, 'problem_1');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @todo: Deprecate in favor of billableProblems()
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

    /**
     * @return \App\Models\CCD\Problem[]|\Illuminate\Database\Eloquent\Collection|static
     */
    public function ccmAttestedProblems()
    {
        return ! $this->hasServiceCode(ChargeableService::BHI)
            ? $this->attestedProblems
            : $this->attestedProblems->where('cpmProblem.is_behavioral', '=', false);
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

    /**
     * @param $userId
     */
    public static function createFromPatient($userId, Carbon $month)
    {
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

            //get last month's services
            $newSummary->attachLastMonthsChargeableServicesIfYouShould($summary);
        } else {
            $newSummary             = new self();
            $newSummary->patient_id = $userId;
        }

        $newSummary->month_year             = $month;
        $newSummary->total_time             = 0;
        $newSummary->ccm_time               = 0;
        $newSummary->bhi_time               = 0;
        $newSummary->no_of_calls            = 0;
        $newSummary->no_of_successful_calls = 0;
        $newSummary->approved               = 0;
        $newSummary->rejected               = 0;
        $newSummary->actor_id               = null;
        $newSummary->needs_qa               = null;
        $newSummary->save();
    }

    public static function existsForCurrentMonthForPatient($patientId): bool
    {
        return (new static())->where('patient_id', $patientId)
            ->where('month_year', Carbon::now()->startOfMonth())
            ->exists();
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

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function practiceHasServiceCode($code): bool
    {
        return (bool) optional($this->patient->primaryPractice)->hasServiceCode($code);
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

    public function shouldGoThroughChargeableServiceAttachmentProcess(): bool
    {
        //process so we can add services depending on time.
        if ($this->chargeableServices->isEmpty()) {
            return true;
        }

        //Just so we can reprocess summary, to remove auto-attached last month's chargeable services and reprocess
        if ( ! $this->approved && ! $this->rejected && ! $this->needs_qa) {
            $this->chargeableServices()->sync([]);

            return true;
        }

        return false;
    }

    public function syncAttestedProblems(array $attestedProblems)
    {
        //remove summary id without detaching. We may still need the association of the problem with the call
        $this->attestedProblems()->update(['call_problems.patient_monthly_summary_id' => null]);

        DB::table('call_problems')
            ->whereNull('call_id')
            ->whereNull('patient_monthly_summary_id')
            ->delete();

        $this->attestedProblems()->attach($attestedProblems);
    }

    /**
     * Get how much time (in seconds) was contributed towards this patient's billable time by CLH Care Coaches.
     */
    public function timeFromClhCareCoaches(): int
    {
        return (int) Activity::createdInMonth($this->month_year, 'performed_at')
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

    private function getBhiProblemsForAutoAttestation()
    {
        return [
            optional($this->patientProblemsSortedByWeight()
                ->first())
                ->id,
        ];
    }

    private function getCcmProblemsForAutoAttestation()
    {
        $patientProblems = $this->patientProblemsSortedByWeight();

        return $this->ccmAttestedProblems()
            ->merge(
                $patientProblems->filter(function (Problem $p) {
                    return ! $this->ccmAttestedProblems()->contains('id', $p->id) && ! $p->isBehavioral();
                })
            )
            ->take(4)
            ->pluck('id')
            ->toArray();
    }

    private function patientProblemsSortedByWeight(): Collection
    {
        $this->loadMissing([
            'patient.ccdProblems' => function ($problems) {
                $problems->with(['icd10codes', 'cpmProblem']);
            },
        ]);

        return $this->patient->ccdProblems->sortByDesc(function ($problem) {
            if ( ! $problem->cpmProblem) {
                return null;
            }

            return $problem->cpmProblem->weight;
        });
    }

    private function unAttestedBhi(): bool
    {
        return $this->hasServiceCode(ChargeableService::BHI) && $this->bhiAttestedProblems()->count() < 1;
    }

    private function unAttestedCcm(): bool
    {
        return $this->hasServiceCode(ChargeableService::CCM) && $this->ccmAttestedProblems()->count() < 2;
    }

    private function unAttestedPcm(): bool
    {
        return $this->hasServiceCode(ChargeableService::PCM) && $this->ccmAttestedProblems()->count() < 1;
    }
}
