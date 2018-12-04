<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Services\WelcomeCallListGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function enrollee()
    {
        return $this->hasOne(Enrollee::class);
    }

    public function getStatus($statusId = null)
    {
        if (!$statusId) {
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

    /**
     * Putting this here for conveniece.
     * It is NOT safe to use as $batch may not exist. Should we make processing without a batch possible?
     *
     * @todo: figure out above, buy beer
     *
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @throws \Exception
     *
     * @return WelcomeCallListGenerator
     */
    public function process($filterLastEncounter, $filterInsurance, $filterProblems)
    {
        return new WelcomeCallListGenerator(
            collect([$this->data]),
            $filterLastEncounter,
            $filterInsurance,
            $filterProblems,
            true,
            $this->batch->practice,
            null,
            null,
            $this->batch,
            $this
        );
    }

    public function scopeEligible($builder)
    {
        return $builder->where('outcome', '=', self::ELIGIBLE);
    }
}
