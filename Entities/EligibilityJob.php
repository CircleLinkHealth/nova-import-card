<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Eligibility\EligibilityChecker;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * CircleLinkHealth\Eligibility\Entities\EligibilityJob.
 *
 * @property int                                                     $id
 * @property int                                                     $batch_id
 * @property string|null                                             $hash
 * @property int|null                                                $status
 * @property array                                                   $data
 * @property string|null                                             $outcome
 * @property string|null                                             $reason
 * @property array                                                   $messages
 * @property array|null                                              $errors
 * @property \Illuminate\Support\Carbon|null                         $last_encounter
 * @property string|null                                             $primary_insurance
 * @property string|null                                             $secondary_insurance
 * @property string|null                                             $tertiary_insurance
 * @property int|null                                                $ccm_problem_1_id
 * @property int|null                                                $ccm_problem_2_id
 * @property int|null                                                $bhi_problem_id
 * @property \Illuminate\Support\Carbon|null                         $created_at
 * @property \Illuminate\Support\Carbon|null                         $updated_at
 * @property string|null                                             $deleted_at
 * @property int                                                     $invalid_data
 * @property int                                                     $invalid_structure
 * @property int                                                     $invalid_mrn
 * @property int                                                     $invalid_first_name
 * @property int                                                     $invalid_last_name
 * @property int                                                     $invalid_dob
 * @property int                                                     $invalid_problems
 * @property int                                                     $invalid_phones
 * @property \CircleLinkHealth\Eligibility\Entities\EligibilityBatch $batch
 * @property \CircleLinkHealth\SharedModels\Entities\Enrollee         $enrollee
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection
 *     $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob eligible()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereBhiProblemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCcmProblem1Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCcmProblem2Id($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereErrors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidDob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidMrn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidPhones($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidProblems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereInvalidStructure($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereLastEncounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereOutcome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob wherePrimaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereSecondaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereTertiaryInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EligibilityJob whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\EligibilityJob withoutTrashed()
 * @mixin \Eloquent
 * @property \App\EligibilityJob                                  $eligibilityJob
 * @property int|null                                             $revision_history_count
 * @property \CircleLinkHealth\Eligibility\Entities\TargetPatient $targetPatient
 * @property string|null                                          $patient_first_name
 * @property string|null                                          $patient_last_name
 * @property string|null                                          $patient_mrn
 * @property string|null                                          $patient_dob
 * @property string|null                                          $patient_email
 */
class EligibilityJob extends BaseModel
{
    use SoftDeletes;

    //Outcome: A patient that exists more than once in the same batch
    const DUPLICATE = 'duplicate';

    //Outcome: A patient that was found to be eligible for the first time in this batch
    const ELIGIBLE = 'eligible';

    //Outcome: A patient that was found to be eligible in this batch, but was also found eligible in a previous batch
    const ELIGIBLE_ALSO_IN_PREVIOUS_BATCH = 'eligible_also_in_previous_batch';

    //Outcome: A patient that was found eligible, but is already an enrolled patient in CPM
    const ENROLLED = 'enrolled';

    //Outcome: Something went wrong during processing.
    const ERROR = 'error';

    //Outcome: An ineligile patient
    const INELIGIBLE = 'ineligible';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];

    protected $attributes = [
        'status' => 0,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'     => 'array',
        'messages' => 'array',
        'errors'   => 'array',
    ];

    protected $dates = [
        'last_encounter',
    ];

    protected $fillable = [
        'batch_id',
        'hash',
        'data',
        'messages',
        'errors',
        'outcome',
        'reason',
        'status',
        'bhi_problem_id',
        'ccm_problem_2_id',
        'ccm_problem_1_id',
        'tertiary_insurance',
        'secondary_insurance',
        'primary_insurance',
        'last_encounter',
        'invalid_data',
        'invalid_structure',
        'invalid_mrn',
        'invalid_first_name',
        'invalid_last_name',
        'invalid_dob',
        'invalid_problems',
        'invalid_phones',
    ];

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class, 'batch_id');
    }

    public function eligibilityJob()
    {
        return $this->belongsTo(EligibilityJob::class);
    }

    public function enrollee()
    {
        return $this->hasOne(Enrollee::class);
    }

    public function getStatus($statusId = null)
    {
        if ( ! $statusId) {
            if (is_null($this->status)) {
                return null;
            }
            $statusId = $this->status;
        }

        foreach (self::STATUSES as $name => $id) {
            if ($id == $statusId) {
                return $name;
            }
        }

        return null;
    }

    public function isAlreadyEnrolled()
    {
        return self::ENROLLED == $this->outcome;
    }

    public function isEligible()
    {
        return self::ELIGIBLE == $this->outcome;
    }

    public function isIneligible()
    {
        return self::INELIGIBLE == $this->outcome;
    }

    /**
     * Process Eligibility With Batch Options.
     *
     * @throws \Exception
     *
     * @return \CircleLinkHealth\Eligibility\EligibilityChecker
     */
    public function process()
    {
        if ( ! $this->batch) {
            throw new \Exception('A batch is necessary to process an eligibility job.');
        }

        return $this->processWithOptions(
            $this->batch->shouldFilterLastEncounter(),
            $this->batch->shouldFilterInsurance(),
            $this->batch->shouldFilterProblems()
        );
    }

    /**
     * Process eligibility with given options.
     *
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @throws \Exception
     *
     * @return \CircleLinkHealth\Eligibility\EligibilityChecker
     */
    public function processWithOptions(
        bool $filterLastEncounter = false,
        bool $filterInsurance = false,
        bool $filterProblems = true
    ) {
        if ( ! $this->batch) {
            throw new \Exception('A batch is necessary to process an eligibility job.');
        }

        return new EligibilityChecker(
            $this,
            $this->batch->practice,
            $this->batch,
            $filterLastEncounter,
            $filterInsurance,
            $filterProblems,
            true
        );
    }

    public function sanitizeDataKeys()
    {
        $this->data = sanitize_array_keys($this->data);
    }

    public function scopeEligible($builder)
    {
        return $builder->where('outcome', '=', self::ELIGIBLE);
    }

    public function targetPatient()
    {
        return $this->hasOne(TargetPatient::class);
    }

    public function wasAlreadyFoundEligibleInAPreviouslyCreatedBatch()
    {
        return self::ELIGIBLE_ALSO_IN_PREVIOUS_BATCH == $this->outcome;
    }
}
