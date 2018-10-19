<?php

namespace App;

use App\Services\WelcomeCallListGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;

class EligibilityJob extends BaseModel
{
    use SoftDeletes;

    const ELIGIBLE = 'eligible';
    const INELIGIBLE = 'ineligible';
    const DUPLICATE = 'duplicate';
    const ENROLLED = 'enrolled';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data'     => 'array',
        'messages' => 'array',
    ];

    protected $fillable = [
        'batch_id',
        'hash',
        'data',
        'messages',
        'outcome',
        'reason',
        'status',
    ];

    protected $attributes = [
        'status' => 0,
    ];

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

    public function scopeEligible($builder)
    {
        return $builder->where('outcome', '=', self::ELIGIBLE);
    }

    public function enrollee()
    {
        return $this->hasOne(Enrollee::class);
    }

    public function batch()
    {
        return $this->belongsTo(EligibilityBatch::class, 'batch_id');
    }

    /**
     * Putting this here for conveniece.
     * It is NOT safe to use as $batch may not exist. Should we make processing without a batch possible?
     * @todo: figure out above, buy beer
     *
     * @param $filterLastEncounter
     * @param $filterInsurance
     * @param $filterProblems
     *
     * @return WelcomeCallListGenerator
     * @throws \Exception
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
}
